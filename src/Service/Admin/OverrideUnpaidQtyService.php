<?php

namespace App\Service\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Entity\InvoiceItemOverridePaymentUnpaidQtyHistory;
use App\Entity\InvoiceOverridePayment;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceOverridePaymentRepository;
use App\Repository\ProjectItemRepository;
use App\Service\Base\Base;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class OverrideUnpaidQtyService extends Base
{
    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        WidgetAccessService $widgetAccessService,
    ) {
        parent::__construct($doctrine, $mailer, $containerBag, $security, $logger, $urlGenerator, $twig, $widgetAccessService);
    }

    /**
     * @return array{data: array<int, array<string, mixed>>, total: int}
     */
    public function Listar(
        int $start,
        int $length,
        string $search,
        string $orderField,
        string $orderDir,
        ?string $company_id,
        ?string $project_id,
        ?string $fecha_fin,
    ): array {
        $endDate = $this->parseDateMDY($fecha_fin);

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);

        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $result = $invoiceItemRepo->ListarParaOverridePaymentConTotal(
            $start,
            $length,
            $search,
            $orderField,
            $orderDir,
            (string) ($company_id ?? ''),
            (string) ($project_id ?? ''),
            $fecha_fin ?? ''
        );

        /** @var list<array<string, mixed>> $raw */
        $raw = $result['data'];
        if ([] === $raw) {
            return ['data' => [], 'total' => $result['total']];
        }

        $piIds = array_map(static fn (array $r) => $r['project_item_id'], $raw);

        /** @var ProjectItem[] $projectItems */
        $projectItems = $projectItemRepo->findBy(['id' => $piIds]);
        $piById = [];
        foreach ($projectItems as $pi) {
            $piById[$pi->getId()] = $pi;
        }

        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $overrideHistRepo */
        $overrideHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);

        $mapOverrideId = [];
        foreach ($piIds as $pid) {
            $oid = null;
            if (null !== $endDate) {
                $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, $endDate);
            }
            if (null === $oid) {
                $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, null);
            }
            $mapOverrideId[$pid] = $oid;
        }

        $overrideIds = array_filter(array_values($mapOverrideId));
        $hasOverrideHistoryMap = [];
        if ([] !== $overrideIds) {
            $historyIds = $overrideHistRepo->IdsConHistorial($overrideIds);
            foreach ($historyIds as $hid) {
                $hasOverrideHistoryMap[$hid] = true;
            }
        }

        $data = [];
        foreach ($raw as $r) {
            $pid = $r['project_item_id'];
            $pi = $piById[$pid] ?? null;
            $item = $pi?->getItem();

            $overrideId = $mapOverrideId[$pid] ?? null;
            $hasHistory = null !== $overrideId && isset($hasOverrideHistoryMap[$overrideId]);

            $data[] = [
                'id' => $r['invoice_item_id'] ?? null,
                'project_item_id' => $pid,
                'invoice_item_override_payment_id' => $overrideId,
                'has_override_unpaid_qty_history' => $hasHistory,
                'item' => $item?->getName(),
                'unit' => $item?->getUnit()?->getDescription(),
                'contract_qty' => $pi?->getQuantity(),
                'price' => $pi?->getPrice(),
                'quantity' => $r['quantity'] ?? null,
                'paid_qty' => $r['paid_qty'] ?? null,
                'unpaid_qty' => $r['unpaid_qty'] ?? null,
                'project_id' => $pi?->getProject()?->getProjectId(),
                'project_number' => $pi?->getProject()?->getProjectNumber(),
                'project_description' => $pi?->getProject()?->getDescription(),
            ];
        }

        return ['data' => $data, 'total' => $result['total']];
    }

    /**
     * @param array<int, array<string, mixed>> $itemsDecoded
     *
     * @return array{success: bool, message?: string, error?: string}
     */
    public function SalvarOverrideUnpaidQty(
        string $project_id,
        string $fecha_fin,
        array $itemsDecoded,
    ): array {
        $project = null;
        $projectIdTrim = trim($project_id);
        if ('' !== $projectIdTrim) {
            $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
            if (null === $project) {
                return ['success' => false, 'error' => 'Project not found'];
            }
        }

        $endDate = $this->parseDateMDY($fecha_fin);
        if (null === $endDate) {
            return ['success' => false, 'error' => 'Select invoice end date'];
        }

        $em = $this->getDoctrine()->getManager();
        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        /** @var InvoiceOverridePaymentRepository $headerRepo */
        $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
        $user = $this->getUser();
        /** @var array<string, InvoiceOverridePayment> */
        $invoiceOverrideHeaderCache = [];

        foreach ($itemsDecoded as $data) {
            $projectItemId = isset($data['project_item_id']) ? (int) $data['project_item_id'] : 0;
            $unpaidQtyNew = isset($data['unpaid_qty']) ? (float) $data['unpaid_qty'] : 0.0;

            if ($projectItemId <= 0) {
                continue;
            }

            $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($projectItemId);
            if (null === $pi) {
                continue;
            }
            if (null !== $project && $pi->getProject()->getProjectId() !== $project->getProjectId()) {
                continue;
            }

            $projEntity = $pi->getProject();
            $header = $this->resolveInvoiceOverridePaymentHeaderForUnpaid($em, $headerRepo, $projEntity, $endDate, $invoiceOverrideHeaderCache);

            $entity = $overrideRepo->findOneBy(
                ['projectItem' => $pi, 'invoiceOverridePayment' => $header],
                ['id' => 'ASC']
            );

            $oldUnpaid = null;
            if (null === $entity) {
                $entity = new InvoiceItemOverridePayment();
                $entity->setProjectItem($pi);
                $entity->setInvoiceOverridePayment($header);
                $entity->setPaidQty(null);
                $entity->setUnpaidQty($unpaidQtyNew);
                $em->persist($entity);

                /** @var InvoiceItemRepository $invoiceItemRepo */
                $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
                $agg = $invoiceItemRepo->aggregateNonBondInvoiceQtyPaidForProjectItem($projectItemId);
                $baselineUnpaid = max(
                    0.0,
                    (float) $agg['sum_qty_final'] - (float) $agg['sum_paid_lines']
                );

                $hist = new InvoiceItemOverridePaymentUnpaidQtyHistory();
                $hist->setInvoiceItemOverridePayment($entity);
                $hist->setOldValue((string) $baselineUnpaid);
                $hist->setNewValue((string) $unpaidQtyNew);
                $hist->setNote(null);
                $hist->setCreatedAt(new \DateTime());
                if ($user instanceof \App\Entity\Usuario) {
                    $hist->setUser($user);
                }
                $em->persist($hist);
            } else {
                $oldUnpaid = $entity->getUnpaidQty();
                $oldFloat = null !== $oldUnpaid ? (float) $oldUnpaid : null;
                if (null === $oldFloat || abs($oldFloat - $unpaidQtyNew) > 0.000001) {
                    $entity->setUnpaidQty($unpaidQtyNew);
                    $entity->setUpdatedAt(new \DateTime());

                    $hist = new InvoiceItemOverridePaymentUnpaidQtyHistory();
                    $hist->setInvoiceItemOverridePayment($entity);
                    $hist->setOldValue(null !== $oldFloat ? (string) $oldFloat : null);
                    $hist->setNewValue((string) $unpaidQtyNew);
                    $hist->setNote(null);
                    $hist->setCreatedAt(new \DateTime());
                    if ($user instanceof \App\Entity\Usuario) {
                        $hist->setUser($user);
                    }
                    $em->persist($hist);
                }
            }
        }

        $em->flush();

        if ([] !== $itemsDecoded) {
            $logMsg = null !== $project
               ? 'Override unpaid qty saved for project #'.$project->getProjectNumber()
               : 'Override unpaid qty saved (multiple projects)';
            $this->SalvarLog('Update', 'Override Unpaid Qty', $logMsg);
        }

        return ['success' => true];
    }

    /**
     * @param array<string, InvoiceOverridePayment> $headerCache
     */
    private function resolveInvoiceOverridePaymentHeaderForUnpaid(
        ObjectManager $em,
        InvoiceOverridePaymentRepository $headerRepo,
        Project $projEntity,
        \DateTimeInterface $endDate,
        array &$headerCache,
    ): InvoiceOverridePayment {
        $key = (string) $projEntity->getProjectId().'|'.$endDate->format('Y-m-d');
        if (isset($headerCache[$key])) {
            return $headerCache[$key];
        }
        $header = $headerRepo->findOneByProjectAndDate((int) $projEntity->getProjectId(), $endDate);
        if (null === $header) {
            $header = new InvoiceOverridePayment();
            $header->setProject($projEntity);
            $header->setDate($endDate);
            $em->persist($header);
        }
        $headerCache[$key] = $header;

        return $header;
    }

    /**
     * @return array<int, array{id:int, mensaje:string, fecha:string, user_name:string, old_value:string, new_value:string}>
     */
    public function ListarHistorialOverrideUnpaidQty(int $invoice_item_override_payment_id): array
    {
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        $lista = $historyRepo->ListarHistorialDeOverride($invoice_item_override_payment_id);

        $historial = [];
        foreach ($lista as $value) {
            $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
            $fecha = $value->getCreatedAt()->format('m/d/Y H:i');
            $old_value_raw = $value->getOldValue();
            $new_value_raw = $value->getNewValue();
            $old_value = null !== $old_value_raw && '' !== $old_value_raw ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
            $new_value = null !== $new_value_raw && '' !== $new_value_raw ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
            $mensaje = "{$fecha} Updated unpaid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";

            $historial[] = [
                'id' => $value->getId(),
                'mensaje' => $mensaje,
                'fecha' => $value->getCreatedAt()->format('m/d/Y'),
                'user_name' => $user_name,
                'old_value' => $old_value,
                'new_value' => $new_value,
            ];
        }

        return $historial;
    }

    private function parseDateMDY(?string $s): ?\DateTimeInterface
    {
        if (null === $s || '' === trim($s)) {
            return null;
        }
        $d = \DateTime::createFromFormat('m/d/Y', trim($s));
        if (false === $d) {
            return null;
        }
        $d->setTime(0, 0, 0);

        return $d;
    }
}
