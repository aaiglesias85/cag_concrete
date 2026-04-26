<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Entity\InvoiceItemOverridePaymentPaidQtyHistory;
use App\Entity\InvoiceItemOverridePaymentUnpaidQtyHistory;
use App\Entity\InvoiceOverridePayment;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Entity\ProjectItemHistory;
use App\Repository\InvoiceItemOverridePaymentPaidQtyHistoryRepository;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceOverridePaymentRepository;
use App\Repository\ProjectItemHistoryRepository;
use App\Repository\ProjectItemRepository;
use App\Utils\Base;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class OverridePaymentService extends Base
{
    public function __construct(
        ContainerInterface $container,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        private ProjectService $projectService,
        private InvoiceService $invoiceService,
    ) {
        parent::__construct($container, $mailer, $containerBag, $security, $logger);
    }

    /**
     * Listado DataTables de cabeceras invoice_override_payment.
     *
     * @return array{data: array<int, array<string, mixed>>, total: int}
     */
    public function ListarCabecerasInvoiceOverridePayment(
        int $start,
        int $limit,
        string $search,
        string $orderField,
        string $orderDir,
        ?string $company_id,
        ?string $project_id,
        ?string $fecha_inicial,
        ?string $fecha_fin,
    ): array {
        /** @var InvoiceOverridePaymentRepository $headerRepo */
        $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
        /** @var InvoiceItemOverridePaymentRepository $itemRepo */
        $itemRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);

        $result = $headerRepo->listarConTotal(
            $start,
            $limit,
            $search,
            $orderField,
            $orderDir,
            (string) ($company_id ?? ''),
            (string) ($project_id ?? ''),
            (string) ($fecha_inicial ?? ''),
            (string) ($fecha_fin ?? '')
        );

        $ids = [];
        foreach ($result['data'] as $h) {
            if (null !== $h->getInvoiceOverridePaymentId()) {
                $ids[] = (int) $h->getInvoiceOverridePaymentId();
            }
        }
        $totals = $itemRepo->aggregateTotalsByHeaderIds($ids);

        $data = [];
        foreach ($result['data'] as $h) {
            $hid = (int) $h->getInvoiceOverridePaymentId();
            $p = $h->getProject();
            $co = null !== $p ? $p->getCompany() : null;
            $t = $totals[$hid] ?? [
                'paidQty' => 0.0,
                'paidAmount' => 0.0,
                'unpaidQty' => 0.0,
                'unpaidAmount' => 0.0,
            ];
            $dateStr = '';
            if (null !== $h->getDate()) {
                $dateStr = $h->getDate()->format('m/d/Y');
            }
            $data[] = [
                'id' => $hid,
                'company' => null !== $co ? (string) ($co->getName() ?? '') : '',
                'project' => null !== $p ? (string) ($p->getDescription() ?? $p->getName() ?? '') : '',
                'projectNumber' => null !== $p ? (string) ($p->getProjectNumber() ?? '') : '',
                'project_id' => null !== $p ? (int) $p->getProjectId() : 0,
                'company_id' => null !== $co ? (int) $co->getCompanyId() : 0,
                'date' => $dateStr,
                'overridePaidQty' => $t['paidQty'],
                'overridePaidAmount' => $t['paidAmount'],
                'overrideUnpaidQty' => $t['unpaidQty'],
                'overrideUnpaidAmount' => $t['unpaidAmount'],
            ];
        }

        return [
            'data' => $data,
            'total' => $result['total'],
        ];
    }

    /**
     * Cabecera `invoice_override_payment` por id para el formulario de edición.
     * Mismo shape que una fila de {@see ListarCabecerasInvoiceOverridePayment} (id, company_id, project_id, date, …).
     *
     * @return array{success: bool, override?: array<string, mixed>, error?: string}
     */
    public function CargarDatosInvoiceOverridePayment(int $invoiceOverridePaymentId): array
    {
        if ($invoiceOverridePaymentId <= 0) {
            return ['success' => false, 'error' => 'Invalid id'];
        }
        $em = $this->getDoctrine()->getManager();
        $h = $em->getRepository(InvoiceOverridePayment::class)->find($invoiceOverridePaymentId);
        if (!$h instanceof InvoiceOverridePayment) {
            return ['success' => false, 'error' => 'Record not found'];
        }
        /** @var InvoiceItemOverridePaymentRepository $itemRepo */
        $itemRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        $hid = (int) $h->getInvoiceOverridePaymentId();
        $totals = $itemRepo->aggregateTotalsByHeaderIds([$hid]);
        $t = $totals[$hid] ?? [
            'paidQty' => 0.0,
            'paidAmount' => 0.0,
            'unpaidQty' => 0.0,
            'unpaidAmount' => 0.0,
        ];
        $p = $h->getProject();
        $co = null !== $p ? $p->getCompany() : null;
        $dateStr = '';
        if (null !== $h->getDate()) {
            $dateStr = $h->getDate()->format('m/d/Y');
        }

        return [
            'success' => true,
            'override' => [
                'id' => $hid,
                'company' => null !== $co ? (string) ($co->getName() ?? '') : '',
                'project' => null !== $p ? (string) ($p->getDescription() ?? $p->getName() ?? '') : '',
                'projectNumber' => null !== $p ? (string) ($p->getProjectNumber() ?? '') : '',
                'project_id' => null !== $p ? (int) $p->getProjectId() : 0,
                'company_id' => null !== $co ? (int) $co->getCompanyId() : 0,
                'date' => $dateStr,
                'overridePaidQty' => $t['paidQty'],
                'overridePaidAmount' => $t['paidAmount'],
                'overrideUnpaidQty' => $t['unpaidQty'],
                'overrideUnpaidAmount' => $t['unpaidAmount'],
            ],
        ];
    }

    /**
     * Elimina la cabecera (cascade a líneas e historiales vía FK / entidades).
     *
     * @return array{success: bool, error?: string}
     */
    public function EliminarCabeceraInvoiceOverridePayment(int $invoiceOverridePaymentId): array
    {
        $em = $this->getDoctrine()->getManager();
        $header = $em->getRepository(InvoiceOverridePayment::class)->find($invoiceOverridePaymentId);
        if (null === $header) {
            return ['success' => false, 'error' => 'Record not found'];
        }
        $em->remove($header);
        $em->flush();
        $this->SalvarLog('Delete', 'Override Payment', 'Override payment header #'.$invoiceOverridePaymentId.' deleted');

        return ['success' => true];
    }

    /**
     * Elimina varias cabeceras `invoice_override_payment` por id (CSV o lista).
     *
     * @return array{success: bool, deleted?: int, requested?: int, error?: string}
     */
    public function EliminarCabecerasInvoiceOverridePayment(string $idsCsv): array
    {
        $parts = array_filter(array_map('intval', explode(',', (string) $idsCsv)));
        if ([] === $parts) {
            return ['success' => false, 'error' => 'No valid IDs'];
        }
        $deleted = 0;
        foreach ($parts as $id) {
            if ($id <= 0) {
                continue;
            }
            $r = $this->EliminarCabeceraInvoiceOverridePayment($id);
            if (!empty($r['success'])) {
                ++$deleted;
            }
        }
        if (0 === $deleted) {
            return ['success' => false, 'error' => 'Could not delete the selected records'];
        }

        return ['success' => true, 'deleted' => $deleted, 'requested' => count($parts)];
    }

    /**
     * Todos los ítems para Override Payment (sin paginación ni búsqueda en servidor).
     * El frontend usa DataTables con datasource local; la búsqueda es en cliente.
     *
     * Los agregados (qty facturada sin QBF en la columna Invoice Qty, paid en líneas, importes, etc.) vienen de
     * facturas con `invoice.start_date` **estrictamente anterior** a la fecha del override (`fecha_fin` en m/d/Y),
     * misma regla que {@see InvoiceItemRepository::ListarParaOverridePaymentConTotal} y el baseline al guardar.
     * Si `fecha_fin` falta o no parsea, se suman todas las facturas del proyecto (comportamiento previo).
     *
     * Para enlazar la fila `invoice_item_override_payment` correcta:
     * - En edición: preferir `invoice_override_payment_id` (cabecera).
     * - Si no hay id de cabecera: `fecha_fin` con {@see InvoiceItemOverridePaymentRepository::BuscarIdPorProjectItemYFechas}
     *   y, si hace falta, búsqueda sin fecha.
     *
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function ListarItemsParaOverridePayment(
        ?string $company_id,
        ?string $project_id,
        ?string $fecha_fin,
        ?int $invoice_override_payment_id = null,
    ): array {
        /** @var InvoiceItemRepository $invoiceItemRepo */
        $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
        $fechaFinAgg = '';
        $parsedFin = $this->parseDateMDY($fecha_fin);
        if (null !== $parsedFin) {
            $fechaFinAgg = $parsedFin->format('m/d/Y');
        }
        $result = $invoiceItemRepo->ListarParaOverridePaymentConTotal(
            0,
            \PHP_INT_MAX,
            '',
            'item',
            'asc',
            (string) ($company_id ?? ''),
            (string) ($project_id ?? ''),
            $fechaFinAgg
        );

        $raw = $result['data'];
        if ([] === $raw) {
            return ['items' => []];
        }

        return ['items' => $this->buildOverridePaymentRowsFromAggregates(
            $raw,
            $fecha_fin,
            $invoice_override_payment_id,
            null !== $project_id ? (string) $project_id : null
        )];
    }

    /**
     * @param array<int, array<string, mixed>> $raw Filas agregadas por project_item desde el repositorio
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildOverridePaymentRowsFromAggregates(
        array $raw,
        ?string $fecha_fin,
        ?int $invoice_override_payment_id,
        ?string $project_id,
    ): array {
        $endDate = $this->parseDateMDY($fecha_fin);

        /** @var ProjectItemRepository $projectItemRepo */
        $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);

        $piIds = array_map(static fn (array $r) => $r['project_item_id'], $raw);

        /** @var ProjectItem[] $projectItems */
        $projectItems = $projectItemRepo->findBy(['id' => $piIds]);
        $piById = [];
        foreach ($projectItems as $pi) {
            $piById[$pi->getId()] = $pi;
        }

        $headerEntity = null;
        if (null !== $invoice_override_payment_id && $invoice_override_payment_id > 0
           && null !== $project_id && '' !== $project_id) {
            $candidate = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class)
               ->find($invoice_override_payment_id);
            if (null !== $candidate) {
                $hp = $candidate->getProject();
                if (null !== $hp && (string) $hp->getProjectId() === (string) $project_id) {
                    $headerEntity = $candidate;
                }
            }
        }

        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $overrideHistRepo */
        $overrideHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $unpaidOverrideHistRepo */
        $unpaidOverrideHistRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        /** @var ProjectItemHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(ProjectItemHistory::class);

        $mapOverrideId = [];
        foreach ($piIds as $pid) {
            $oid = null;
            if (null !== $headerEntity) {
                $piEntity = $piById[$pid] ?? null;
                if (null !== $piEntity) {
                    $line = $overrideRepo->findOneBy([
                        'projectItem' => $piEntity,
                        'invoiceOverridePayment' => $headerEntity,
                    ]);
                    $oid = null !== $line ? $line->getId() : null;
                }
            }
            if (null === $oid && null !== $endDate) {
                $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, $endDate);
            }
            if (null === $oid) {
                $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, null);
            }
            $mapOverrideId[$pid] = $oid;
        }
        $overrideIds = array_values(array_filter($mapOverrideId, static fn ($id) => null !== $id));
        $overrideConHist = array_fill_keys($overrideHistRepo->IdsConHistorial($overrideIds), true);
        $unpaidOverrideConHist = array_fill_keys($unpaidOverrideHistRepo->IdsConHistorial($overrideIds), true);

        $paidQtyByPi = [];
        $unpaidExplicitByPi = [];
        if ([] !== $overrideIds) {
            /** @var InvoiceItemOverridePayment[] $overrides */
            $overrides = $overrideRepo->findBy(['id' => $overrideIds]);
            foreach ($overrides as $ov) {
                $opi = $ov->getProjectItem();
                if (null === $opi) {
                    continue;
                }
                if (null !== $ov->getPaidQty()) {
                    $paidQtyByPi[$opi->getId()] = (float) $ov->getPaidQty();
                }
                if (null !== $ov->getUnpaidQty()) {
                    $unpaidExplicitByPi[$opi->getId()] = (float) $ov->getUnpaidQty();
                }
            }
        }

        $histQtyCache = [];
        $histPriceCache = [];

        $rows = [];
        foreach ($raw as $r) {
            $r = array_change_key_case((array) $r, CASE_LOWER);
            $pid = (int) ($r['project_item_id'] ?? 0);
            $pi = $piById[$pid] ?? null;
            if (null === $pi) {
                continue;
            }

            $itemEntity = $pi->getItem();
            if (null === $itemEntity) {
                continue;
            }

            if (!isset($histQtyCache[$pid])) {
                $histQtyCache[$pid] = $historyRepo->TieneHistorialCantidad($pid);
            }
            if (!isset($histPriceCache[$pid])) {
                $histPriceCache[$pid] = $historyRepo->TieneHistorialPrecio($pid);
            }

            $sumQtyFinal = (float) ($r['sum_qty_final'] ?? 0);
            $contractQty = (float) ($pi->getQuantity() ?? 0);
            $priceContract = (float) ($pi->getPrice() ?? 0);

            $cutoffYmd = null;
            if (null !== $endDate) {
                $cutoffYmd = $endDate->format('Y-m-d');
            }

            if (array_key_exists($pid, $paidQtyByPi)) {
                $paidQtyDisplay = $paidQtyByPi[$pid];
                $paidAmount = $paidQtyDisplay * $priceContract;
            } else {
                $aggPaid = $this->projectService->computeInvoiceStyleCumulativePaidBeforeCutoffExclusive($pid, $cutoffYmd);
                $paidQtyDisplay = $aggPaid['total_paid_effective'];
                $paidAmount = $aggPaid['paid_amount_total'];
            }

            $overrideId = $mapOverrideId[$pid] ?? null;
            if (null !== $overrideId && array_key_exists($pid, $unpaidExplicitByPi)) {
                $unpaidQty = $unpaidExplicitByPi[$pid];
            } else {
                // Para override nuevo: unpaid = invoice qty total - paid total
                // Esto es el unpaid calculado, no el valor persistido en el último invoice
                $unpaidQty = max(0.0, $sumQtyFinal - $paidQtyDisplay);
            }

            $hasPaidOverrideHist = null !== $overrideId && isset($overrideConHist[$overrideId]);
            $hasUnpaidOverrideHist = null !== $overrideId && isset($unpaidOverrideConHist[$overrideId]);
            $unpaidQtyReadonly = null !== $overrideId
               && (array_key_exists($pid, $unpaidExplicitByPi) || $hasUnpaidOverrideHist);

            $codStr = null !== $pi->getChangeOrderDate()
               ? $pi->getChangeOrderDate()->format('m/d/Y')
               : '';

            $rows[] = [
                'project_item_id' => $pid,
                'item_id' => (int) ($itemEntity->getItemId() ?? 0),
                'invoice_item_override_payment_id' => $overrideId,
                'apply_retainage' => $pi->getApplyRetainage() ? 1 : 0,
                'bonded' => $pi->getBonded() ? 1 : 0,
                'bond' => $itemEntity->getBond() ? 1 : 0,
                'code' => $pi->getCode() ?? '',
                'item' => $itemEntity->getName() ?? '',
                'unit' => $itemEntity->getUnit() ? $itemEntity->getUnit()->getDescription() : '',
                'contract_qty' => $contractQty,
                'price' => $priceContract,
                'contract_amount' => $contractQty * $priceContract,
                'quantity' => $sumQtyFinal,
                'quantity_completed' => (float) ($r['sum_qty_completed'] ?? 0),
                'amount' => (float) ($r['sum_amount'] ?? 0),
                'total_amount' => (float) ($r['sum_total_amount'] ?? 0),
                'paid_qty' => $paidQtyDisplay,
                'unpaid_qty' => $unpaidQty,
                'paid_amount' => $paidAmount,
                'paid_amount_total' => $paidAmount,
                'principal' => $pi->getPrincipal() ? 1 : 0,
                'change_order' => $pi->getChangeOrder() ? 1 : 0,
                'change_order_date' => $codStr,
                'has_quantity_history' => $histQtyCache[$pid],
                'has_price_history' => $histPriceCache[$pid],
                'has_override_payment_history' => $hasPaidOverrideHist,
                'has_override_unpaid_qty_history' => $hasUnpaidOverrideHist,
                'unpaid_qty_readonly' => $unpaidQtyReadonly,
            ];
        }

        return $rows;
    }

    /**
     * @param array<int, array<string, mixed>> $itemsDecoded
     */
    public function SalvarOverridePayment(
        string $project_id,
        string $fecha_fin,
        array $itemsDecoded,
        ?int $invoice_override_payment_id = null,
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

        $editHeader = null;
        if (null !== $invoice_override_payment_id && $invoice_override_payment_id > 0) {
            $editHeader = $em->getRepository(InvoiceOverridePayment::class)->find($invoice_override_payment_id);
            if (!$editHeader instanceof InvoiceOverridePayment) {
                return ['success' => false, 'error' => 'Override header not found'];
            }
            if (null === $project) {
                return ['success' => false, 'error' => 'Project required'];
            }
            $hdrProject = $editHeader->getProject();
            $hdrPid = null !== $hdrProject ? (int) $hdrProject->getProjectId() : 0;
            $formPid = (int) $project->getProjectId();
            if ($formPid !== $hdrPid) {
                if ($editHeader->getItemOverrides()->count() > 0) {
                    return [
                        'success' => false,
                        'error' => 'No se puede cambiar el proyecto: el override ya tiene líneas de ítems.',
                    ];
                }
                $editHeader->setProject($project);
            }
            $editHeader->setDate($endDate);
            $other = $headerRepo->findOneByProjectAndDate($formPid, $endDate);
            if (null !== $other
               && (int) ($other->getInvoiceOverridePaymentId() ?? 0) !== (int) $invoice_override_payment_id) {
                return [
                    'success' => false,
                    'error' => 'Ya existe un override de pago para este proyecto y fecha.',
                ];
            }
        }

        if (null !== $editHeader && [] === $itemsDecoded) {
            try {
                $em->flush();
            } catch (\Throwable $e) {
                if (!$this->isUniqueConstraintViolation($e)) {
                    throw $e;
                }

                return [
                    'success' => false,
                    'error' => 'Ya existe un override de pago para este proyecto y fecha.',
                ];
            }
            $this->SalvarLog(
                'Update',
                'Override Payment',
                'Override payment header saved for project #'.$project->getProjectNumber()
            );

            return ['success' => true];
        }
        /** @var array<string, InvoiceOverridePayment> Reutiliza cabecera creada en el mismo request (findOneByProjectAndDate no ve inserts sin flush). */
        $invoiceOverrideHeaderCache = [];

        foreach ($itemsDecoded as $data) {
            if (!is_array($data)) {
                continue;
            }
            $projectItemId = isset($data['project_item_id']) ? (int) $data['project_item_id'] : 0;
            $hasPaidQty = isset($data['paid_qty']);
            $paidQtyNew = $hasPaidQty ? (float) $data['paid_qty'] : 0.0;

            if ($projectItemId <= 0 || !$hasPaidQty) {
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
            if (null !== $editHeader) {
                $header = $editHeader;
            } else {
                $header = $this->resolveInvoiceOverridePaymentHeader($em, $headerRepo, $projEntity, $endDate, $invoiceOverrideHeaderCache);
            }

            $entity = $overrideRepo->findOneBy(
                ['projectItem' => $pi, 'invoiceOverridePayment' => $header],
                ['id' => 'ASC']
            );

            $oldPaid = null;
            if (null === $entity) {
                $entity = new InvoiceItemOverridePayment();
                $entity->setProjectItem($pi);
                $entity->setInvoiceOverridePayment($header);
                $entity->setPaidQty($paidQtyNew);
                $entity->setUnpaidQty(null);
                $em->persist($entity);

                $hist = new InvoiceItemOverridePaymentPaidQtyHistory();
                $hist->setInvoiceItemOverridePayment($entity);
                $hist->setOldValue((string) $this->baselinePaidFromInvoiceLinesNonBond($pi, $endDate));
                $hist->setNewValue((string) $paidQtyNew);
                $hist->setCreatedAt(new \DateTime());
                if ($user instanceof \App\Entity\Usuario) {
                    $hist->setUser($user);
                }
                $em->persist($hist);
            } else {
                $storedPaid = $entity->getPaidQty();
                $oldPaid = null === $storedPaid
                   ? $this->baselinePaidFromInvoiceLinesNonBond($pi, $endDate)
                   : (float) $storedPaid;
                if (abs($oldPaid - $paidQtyNew) > 0.000001) {
                    $entity->setPaidQty($paidQtyNew);
                    $entity->setUpdatedAt(new \DateTime());

                    $hist = new InvoiceItemOverridePaymentPaidQtyHistory();
                    $hist->setInvoiceItemOverridePayment($entity);
                    $hist->setOldValue((string) $oldPaid);
                    $hist->setNewValue((string) $paidQtyNew);
                    $hist->setCreatedAt(new \DateTime());
                    if ($user instanceof \App\Entity\Usuario) {
                        $hist->setUser($user);
                    }
                    $em->persist($hist);
                }
            }
        }

        try {
            $em->flush();
        } catch (\Throwable $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            return [
                'success' => false,
                'error' => 'Ya existe un override de pago para este proyecto y fecha.',
            ];
        }

        if ([] !== $itemsDecoded) {
            $logMsg = null !== $project
               ? 'Override payment quantities saved for project #'.$project->getProjectNumber()
               : 'Override payment quantities saved (multiple projects)';
            $this->SalvarLog('Update', 'Override Payment', $logMsg);
        }

        return ['success' => true];
    }

    /**
     * Lista notas (filas de historial con note) para el modal, estilo Payments.
     *
     * @return array{success: bool, error?: string, notes?: array<int, array<string, mixed>>, invoice_item_override_payment_id?: int|null}
     */
    public function ListarNotasOverrideUnpaidQty(
        string $project_id,
        string $fecha_fin,
        int $project_item_id,
    ): array {
        $projectIdTrim = trim($project_id);
        if ('' === $projectIdTrim) {
            return ['success' => false, 'error' => 'Project required'];
        }
        $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
        if (null === $project) {
            return ['success' => false, 'error' => 'Project not found'];
        }
        $endDate = $this->parseDateMDY($fecha_fin);
        if (null === $endDate) {
            return ['success' => false, 'error' => 'Select invoice end date'];
        }
        $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
        if (null === $pi || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
            return ['success' => false, 'error' => 'Project item not found'];
        }
        $parent = $this->findPaymentOverrideForProjectItemEndDate($pi, $endDate);
        if (null === $parent || null === $parent->getId()) {
            return ['success' => true, 'notes' => [], 'invoice_item_override_payment_id' => null];
        }
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        $lista = $histRepo->ListarHistorialDeOverride((int) $parent->getId());
        $notes = [];
        foreach ($lista as $i => $h) {
            if (!$h instanceof InvoiceItemOverridePaymentUnpaidQtyHistory) {
                continue;
            }
            $nv = $h->getNewValue();
            $notes[] = [
                'id' => $h->getId(),
                'notes' => $h->getNote() ?? '',
                'date' => null !== $h->getCreatedAt() ? $h->getCreatedAt()->format('m/d/Y') : '',
                'override_unpaid_qty' => null !== $nv && '' !== $nv ? (float) $nv : null,
                'posicion' => $i,
            ];
        }

        return [
            'success' => true,
            'notes' => $notes,
            'invoice_item_override_payment_id' => (int) $parent->getId(),
        ];
    }

    /**
     * Agrega una fila al historial (nueva nota) o edita una existente. La nota (HTML) vive en history.note.
     *
     * @return array{success: bool, error?: string, note?: array{id: int, date: string, override_unpaid_qty: float}, invoice_item_override_payment_id?: int}
     */
    public function SalvarNotaOverrideUnpaidQty(
        string $project_id,
        string $fecha_fin,
        int $project_item_id,
        string $notesHtml,
        ?string $override_unpaid_qty_raw,
        ?int $history_id = null,
        ?string $override_unpaid_qty_previous_raw = null,
    ): array {
        $projectIdTrim = trim($project_id);
        if ('' === $projectIdTrim) {
            return ['success' => false, 'error' => 'Project required'];
        }
        $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
        if (null === $project) {
            return ['success' => false, 'error' => 'Project not found'];
        }

        $endDate = $this->parseDateMDY($fecha_fin);
        if (null === $endDate) {
            return ['success' => false, 'error' => 'Select invoice end date'];
        }

        $plain = trim(strip_tags($notesHtml ?? ''));
        if ('' === $plain) {
            return ['success' => false, 'error' => 'The note cannot be empty'];
        }

        if (null === $override_unpaid_qty_raw || '' === $override_unpaid_qty_raw) {
            return ['success' => false, 'error' => 'Override unpaid qty is required'];
        }
        $unpaidQtyNew = (float) $override_unpaid_qty_raw;
        if ($unpaidQtyNew < 0) {
            return ['success' => false, 'error' => 'Override unpaid qty must be >= 0'];
        }

        $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
        if (null === $pi || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
            return ['success' => false, 'error' => 'Project item not found'];
        }

        $em = $this->getDoctrine()->getManager();
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        $user = $this->getUser();

        if (null !== $history_id && $history_id > 0) {
            $hist = $histRepo->find($history_id);
            if (!$hist instanceof InvoiceItemOverridePaymentUnpaidQtyHistory) {
                return ['success' => false, 'error' => 'History entry not found'];
            }
            $parent = $hist->getInvoiceItemOverridePayment();
            if (null === $parent || $parent->getProjectItem()?->getId() !== $pi->getId()) {
                return ['success' => false, 'error' => 'Invalid history entry'];
            }
            $hist->setNote($notesHtml);
            $hist->setNewValue((string) $unpaidQtyNew);
            try {
                $em->flush();
                $this->syncPaymentUnpaidFromLatestHistory($parent, $em);
                $em->flush();
            } catch (\Throwable $e) {
                if (!$this->isUniqueConstraintViolation($e)) {
                    throw $e;
                }

                return [
                    'success' => false,
                    'error' => 'Ya existe un override de pago para este proyecto y fecha.',
                ];
            }

            $logMsg = 'Unpaid qty override note updated for project #'.$project->getProjectNumber();
            $this->SalvarLog('Update', 'Override Payment', $logMsg);

            return [
                'success' => true,
                'invoice_item_override_payment_id' => (int) $parent->getId(),
                'note' => [
                    'id' => (int) $hist->getId(),
                    'date' => null !== $hist->getCreatedAt() ? $hist->getCreatedAt()->format('m/d/Y') : '',
                    'override_unpaid_qty' => $unpaidQtyNew,
                ],
            ];
        }

        $parent = $this->findPaymentOverrideForProjectItemEndDate($pi, $endDate);
        if (null === $parent) {
            /** @var InvoiceOverridePaymentRepository $headerRepo */
            $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
            $projEntity = $pi->getProject();
            $headerCache = [];
            $header = $this->resolveInvoiceOverridePaymentHeader($em, $headerRepo, $projEntity, $endDate, $headerCache);
            $parent = new InvoiceItemOverridePayment();
            $parent->setProjectItem($pi);
            $parent->setInvoiceOverridePayment($header);
            $parent->setPaidQty(null);
            $parent->setUnpaidQty(null);
            $em->persist($parent);
        }

        $oldStr = null;
        if (null !== $override_unpaid_qty_previous_raw && '' !== trim($override_unpaid_qty_previous_raw)) {
            $prevTrim = trim($override_unpaid_qty_previous_raw);
            if (is_numeric($prevTrim)) {
                $oldStr = (string) (float) $prevTrim;
            }
        }
        if (null === $oldStr) {
            if (null !== $parent->getUnpaidQty()) {
                $oldStr = (string) $parent->getUnpaidQty();
            } else {
                $oldStr = (string) $this->invoiceService->getUnpaidQtyMatchingInvoiceListarForLastLineBeforeCutoff(
                    (int) $pi->getId(),
                    $endDate->format('Y-m-d')
                );
            }
        }

        $unpaidHist = new InvoiceItemOverridePaymentUnpaidQtyHistory();
        $unpaidHist->setInvoiceItemOverridePayment($parent);
        $unpaidHist->setOldValue($oldStr);
        $unpaidHist->setNewValue((string) $unpaidQtyNew);
        $unpaidHist->setNote($notesHtml);
        $unpaidHist->setCreatedAt(new \DateTime());
        if ($user instanceof \App\Entity\Usuario) {
            $unpaidHist->setUser($user);
        }
        $em->persist($unpaidHist);
        try {
            $em->flush();
            $this->syncPaymentUnpaidFromLatestHistory($parent, $em);
            $em->flush();
        } catch (\Throwable $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            return [
                'success' => false,
                'error' => 'Ya existe un override de pago para este proyecto y fecha.',
            ];
        }

        $logMsg = 'Unpaid qty override note saved for project #'.$project->getProjectNumber();
        $this->SalvarLog('Update', 'Override Payment', $logMsg);

        return [
            'success' => true,
            'invoice_item_override_payment_id' => (int) $parent->getId(),
            'note' => [
                'id' => (int) $unpaidHist->getId(),
                'date' => $unpaidHist->getCreatedAt()->format('m/d/Y'),
                'override_unpaid_qty' => $unpaidQtyNew,
            ],
        ];
    }

    /**
     * Elimina una nota (fila de historial). Si no quedan filas, elimina el override padre.
     *
     * @return array{success: bool, error?: string}
     */
    public function EliminarNotaOverrideUnpaidQty(
        string $project_id,
        int $project_item_id,
        int $history_id,
    ): array {
        $projectIdTrim = trim($project_id);
        if ('' === $projectIdTrim || $history_id <= 0) {
            return ['success' => false, 'error' => 'Invalid request'];
        }
        $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
        if (null === $project) {
            return ['success' => false, 'error' => 'Project not found'];
        }
        $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
        if (null === $pi || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
            return ['success' => false, 'error' => 'Project item not found'];
        }

        $em = $this->getDoctrine()->getManager();
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
        $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);

        $hist = $histRepo->find($history_id);
        if (!$hist instanceof InvoiceItemOverridePaymentUnpaidQtyHistory) {
            return ['success' => false, 'error' => 'History entry not found'];
        }
        $parent = $hist->getInvoiceItemOverridePayment();
        if (null === $parent || $parent->getProjectItem()?->getId() !== $pi->getId()) {
            return ['success' => false, 'error' => 'Invalid history entry'];
        }

        $overrideId = (int) $parent->getId();
        $em->remove($hist);
        $em->flush();

        $parentReload = $overrideRepo->find($overrideId);
        if ($parentReload instanceof InvoiceItemOverridePayment) {
            $this->syncPaymentUnpaidFromLatestHistory($parentReload, $em);
        }
        $em->flush();

        $this->SalvarLog('Update', 'Override Payment', 'Unpaid qty override note deleted for project #'.$project->getProjectNumber());

        return ['success' => true];
    }

    /**
     * @return array<int, array{id:int, mensaje:string, fecha:string, user_name:string, old_value:string, new_value:string}>
     */
    public function ListarHistorialOverridePayment(int $invoice_item_override_payment_id): array
    {
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $historyRepo */
        $historyRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        $lista = $historyRepo->ListarHistorialDeOverride($invoice_item_override_payment_id);

        $historial = [];
        foreach ($lista as $value) {
            $user_name = $value->getUser() ? $value->getUser()->getNombreCompleto() : 'Unknown';
            $fecha = $value->getCreatedAt()->format('m/d/Y H:i');
            $old_value_raw = $value->getOldValue();
            $new_value_raw = $value->getNewValue();
            $old_value = null !== $old_value_raw && '' !== $old_value_raw ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
            $new_value = null !== $new_value_raw && '' !== $new_value_raw ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
            $mensaje = "{$fecha} Updated paid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";

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

    public function ListarHistorialOverrideUnpaidQty(int $invoice_item_override_payment_id): array
    {
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
            $notaTxt = $value->getNote();
            $notaResumen = '';
            if (null !== $notaTxt && '' !== trim(strip_tags($notaTxt))) {
                $strip = trim(strip_tags($notaTxt));
                $notaResumen = mb_strlen($strip) > 120 ? mb_substr($strip, 0, 120).'…' : $strip;
            }
            $mensaje = "{$fecha} Updated unpaid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
            if ('' !== $notaResumen) {
                $mensaje .= " · Note: {$notaResumen}";
            }

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

    /**
     * Historial de cambios de paid_qty (invoice_item_override_payment_paid_qty_history) de ítems del proyecto.
     * Misma forma que {@see ProjectService::ListarInvoiceItemOverridePaymentHistoryDeProject} para el tab Historial.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ListarHistorialOverridePaymentProyecto(int $project_id): array
    {
        $out = [];
        /** @var InvoiceItemOverridePaymentPaidQtyHistoryRepository $histRepo */
        $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentPaidQtyHistory::class);
        $rows = $histRepo->ListarPorProject($project_id);
        foreach ($rows as $key => $h) {
            $override = $h->getInvoiceItemOverridePayment();
            $pi = null !== $override ? $override->getProjectItem() : null;
            $item = null !== $pi ? $pi->getItem() : null;
            $user = $h->getUser();
            $userName = null !== $user ? $user->getNombreCompleto() : 'Unknown';
            $oldRaw = $h->getOldValue();
            $newRaw = $h->getNewValue();
            $oldQty = null !== $oldRaw && '' !== $oldRaw ? number_format((float) $oldRaw, 2, '.', ',') : '—';
            $newQty = null !== $newRaw && '' !== $newRaw ? number_format((float) $newRaw, 2, '.', ',') : '—';
            $created = $h->getCreatedAt();
            $out[] = [
                'id' => $h->getId(),
                'item_description' => null !== $item ? (string) ($item->getDescription() ?? '') : '',
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'user_name' => $userName,
                'created_at' => null !== $created ? $created->format('m/d/Y H:i') : '',
                'posicion' => $key,
            ];
        }

        return $out;
    }

    private function isUniqueConstraintViolation(\Throwable $e): bool
    {
        $cur = $e;
        for ($i = 0; $i < 6 && null !== $cur; ++$i) {
            if ($cur instanceof UniqueConstraintViolationException) {
                return true;
            }
            $cur = $cur->getPrevious();
        }

        return false;
    }

    /**
     * Cabecera única por proyecto y fecha; reutiliza la creada en el mismo request antes del flush.
     *
     * @param array<string, InvoiceOverridePayment> $headerCache
     */
    private function resolveInvoiceOverridePaymentHeader(
        EntityManagerInterface $em,
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

    private function findPaymentOverrideForProjectItemEndDate(
        ProjectItem $pi,
        \DateTimeInterface $endDate,
    ): ?InvoiceItemOverridePayment {
        /** @var InvoiceOverridePaymentRepository $headerRepo */
        $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
        /** @var InvoiceItemOverridePaymentRepository $repo */
        $repo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
        $projectId = (int) $pi->getProject()->getProjectId();
        foreach ([$endDate, null] as $d) {
            $header = $headerRepo->findOneByProjectAndDate($projectId, $d);
            if (null === $header) {
                continue;
            }
            $e = $repo->findOneBy(
                ['projectItem' => $pi, 'invoiceOverridePayment' => $header],
                ['id' => 'ASC']
            );
            if ($e instanceof InvoiceItemOverridePayment) {
                return $e;
            }
        }

        return null;
    }

    private function syncPaymentUnpaidFromLatestHistory(
        InvoiceItemOverridePayment $payment,
        EntityManagerInterface $em,
    ): void {
        $pid = $payment->getId();
        if (null === $pid) {
            return;
        }
        /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
        $histRepo = $em->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
        $latest = $histRepo->findLatestByOverrideId((int) $pid);
        if (null === $latest) {
            $payment->setUnpaidQty(null);
            $payment->setUpdatedAt(new \DateTime());

            return;
        }
        $nv = $latest->getNewValue();
        $payment->setUnpaidQty(null !== $nv && '' !== $nv ? (float) $nv : null);
        $payment->setUpdatedAt(new \DateTime());
    }

    /**
     * Paid “de sistema” antes de fijar paid en esta fila: **mismo cálculo que la columna Pay Quantity**
     * de la grilla ({@see ProjectService::computeInvoiceStyleCumulativePaidBeforeCutoffExclusive} — paid efectivo
     * por línea vía resolver, sin ítems bond, `invoice.start_date` &lt; fecha del override).
     *
     * No usar solo SUM(invoice_item.paid_qty): si hay overrides previos en el histórico, el efectivo puede
     * diferir y el historial de paid quedaría 0 → X aunque la pantalla muestre 300 → X.
     */
    private function baselinePaidFromInvoiceLinesNonBond(ProjectItem $pi, ?\DateTimeInterface $startOverride = null): float
    {
        $ymd = null !== $startOverride ? $startOverride->format('Y-m-d') : null;
        $agg = $this->projectService->computeInvoiceStyleCumulativePaidBeforeCutoffExclusive((int) $pi->getId(), $ymd);

        return (float) ($agg['total_paid_effective'] ?? 0);
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
