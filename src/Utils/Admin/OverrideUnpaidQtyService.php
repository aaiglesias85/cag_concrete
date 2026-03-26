<?php

namespace App\Utils\Admin;

use App\Entity\InvoiceItem;
use App\Entity\InvoiceItemOverridePayment;
use App\Entity\InvoiceItemOverridePaymentUnpaidQtyHistory;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Repository\InvoiceItemOverridePaymentRepository;
use App\Repository\InvoiceItemOverridePaymentUnpaidQtyHistoryRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\ProjectItemRepository;
use App\Utils\Base;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OverrideUnpaidQtyService extends Base
{
   public function __construct(
      ContainerInterface $container,
      MailerInterface $mailer,
      ContainerBagInterface $containerBag,
      Security $security,
      LoggerInterface $logger
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
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
      ?string $fecha_fin
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

      $raw = $result['data'];
      if ($raw === []) {
         return ['data' => [], 'total' => $result['total']];
      }

      $piIds = array_map(static fn(array $r) => $r['project_item_id'], $raw);

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
         if ($endDate !== null) {
            $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, $endDate);
         }
         if ($oid === null) {
            $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, null);
         }
         $mapOverrideId[$pid] = $oid;
      }

      $overrideIds = array_filter(array_values($mapOverrideId));
      $hasOverrideHistoryMap = [];
      if ($overrideIds !== []) {
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
         $hasHistory = $overrideId !== null && isset($hasOverrideHistoryMap[$overrideId]);

         $data[] = [
            'id' => $r['invoice_item_id'] ?? null,
            'project_item_id' => $pid,
            'invoice_item_override_payment_id' => $overrideId,
            'has_override_unpaid_qty_history' => $hasHistory,
            'item' => $item?->getItem(),
            'unit' => $pi?->getUnit()?->getAbrev(),
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
    * @return array{success: bool, message?: string, error?: string}
    */
   public function SalvarOverrideUnpaidQty(
      string $project_id,
      string $fecha_fin,
      array $itemsDecoded
   ): array {
      $project = null;
      $projectIdTrim = trim($project_id);
      if ($projectIdTrim !== '') {
         $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
         if ($project === null) {
            return ['success' => false, 'error' => 'Project not found'];
         }
      }

      $endDate = $this->parseDateMDY($fecha_fin);
      if ($endDate === null) {
         return ['success' => false, 'error' => 'Select invoice end date'];
      }

      $em = $this->getDoctrine()->getManager();
      /** @var InvoiceItemOverridePaymentRepository $overrideRepo */
      $overrideRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
      $user = $this->getUser();

      foreach ($itemsDecoded as $data) {
         if (!is_array($data)) {
            continue;
         }
         $projectItemId = isset($data['project_item_id']) ? (int) $data['project_item_id'] : 0;
         $unpaidQtyNew = isset($data['unpaid_qty']) ? (float) $data['unpaid_qty'] : 0.0;

         if ($projectItemId <= 0) {
            continue;
         }

         $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($projectItemId);
         if ($pi === null) {
            continue;
         }
         if ($project !== null && $pi->getProject()->getProjectId() !== $project->getProjectId()) {
            continue;
         }

         $entity = $overrideRepo->findOneBy([
            'projectItem' => $pi,
            'startDate' => null,
            'endDate' => $endDate,
         ], ['id' => 'ASC']);

         $oldUnpaid = null;
         if ($entity === null) {
            $entity = new InvoiceItemOverridePayment();
            $entity->setProjectItem($pi);
            $entity->setStartDate(null);
            $entity->setEndDate($endDate);
            $entity->setPaidQty(0.0);
            $entity->setUnpaidQty($unpaidQtyNew);
            $em->persist($entity);

            $hist = new InvoiceItemOverridePaymentUnpaidQtyHistory();
            $hist->setInvoiceItemOverridePayment($entity);
            $hist->setOldValue(null);
            $hist->setNewValue((string) $unpaidQtyNew);
            $hist->setNote(null);
            $hist->setCreatedAt(new \DateTime());
            if ($user instanceof \App\Entity\Usuario) {
               $hist->setUser($user);
            }
            $em->persist($hist);
         } else {
            $oldUnpaid = $entity->getUnpaidQty();
            $oldFloat = $oldUnpaid !== null ? (float) $oldUnpaid : null;
            if ($oldFloat === null || abs($oldFloat - $unpaidQtyNew) > 0.000001) {
               $entity->setUnpaidQty($unpaidQtyNew);
               $entity->setUpdatedAt(new \DateTime());

               $hist = new InvoiceItemOverridePaymentUnpaidQtyHistory();
               $hist->setInvoiceItemOverridePayment($entity);
               $hist->setOldValue($oldFloat !== null ? (string) $oldFloat : null);
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

      if ($itemsDecoded !== []) {
         $logMsg = $project !== null
            ? 'Override unpaid qty saved for project #' . $project->getProjectNumber()
            : 'Override unpaid qty saved (multiple projects)';
         $this->SalvarLog('Update', 'Override Unpaid Qty', $logMsg);
      }

      return ['success' => true];
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
         $old_value = $old_value_raw !== null && $old_value_raw !== '' ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
         $new_value = $new_value_raw !== null && $new_value_raw !== '' ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
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
      if ($s === null || trim($s) === '') {
         return null;
      }
      $d = \DateTime::createFromFormat('m/d/Y', trim($s));
      if ($d === false) {
         return null;
      }
      $d->setTime(0, 0, 0);

      return $d;
   }
}
