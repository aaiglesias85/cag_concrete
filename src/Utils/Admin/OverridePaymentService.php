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
use App\Repository\InvoiceOverridePaymentRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\ProjectItemHistoryRepository;
use App\Repository\ProjectItemRepository;
use App\Utils\Base;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OverridePaymentService extends Base
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
      ?string $fecha_fin
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
         if ($h->getInvoiceOverridePaymentId() !== null) {
            $ids[] = (int) $h->getInvoiceOverridePaymentId();
         }
      }
      $totals = $itemRepo->aggregateTotalsByHeaderIds($ids);

      $data = [];
      foreach ($result['data'] as $h) {
         $hid = (int) $h->getInvoiceOverridePaymentId();
         $p = $h->getProject();
         $co = $p !== null ? $p->getCompany() : null;
         $t = $totals[$hid] ?? [
            'paidQty' => 0.0,
            'paidAmount' => 0.0,
            'unpaidQty' => 0.0,
            'unpaidAmount' => 0.0,
         ];
         $dateStr = '';
         if ($h->getDate() !== null) {
            $dateStr = $h->getDate()->format('m/d/Y');
         }
         $data[] = [
            'id' => $hid,
            'company' => $co !== null ? (string) ($co->getName() ?? '') : '',
            'project' => $p !== null ? (string) ($p->getDescription() ?? $p->getName() ?? '') : '',
            'projectNumber' => $p !== null ? (string) ($p->getProjectNumber() ?? '') : '',
            'project_id' => $p !== null ? (int) $p->getProjectId() : 0,
            'company_id' => $co !== null ? (int) $co->getCompanyId() : 0,
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
    * Elimina la cabecera (cascade a líneas e historiales vía FK / entidades).
    *
    * @return array{success: bool, error?: string}
    */
   public function EliminarCabeceraInvoiceOverridePayment(int $invoiceOverridePaymentId): array
   {
      $em = $this->getDoctrine()->getManager();
      $header = $em->getRepository(InvoiceOverridePayment::class)->find($invoiceOverridePaymentId);
      if ($header === null) {
         return ['success' => false, 'error' => 'Record not found'];
      }
      $em->remove($header);
      $em->flush();
      $this->SalvarLog('Delete', 'Override Payment', 'Override payment header #' . $invoiceOverridePaymentId . ' deleted');

      return ['success' => true];
   }

   /**
    * Todos los ítems para Override Payment (sin paginación ni búsqueda en servidor).
    * El frontend usa DataTables con datasource local; la búsqueda es en cliente.
    *
    * `fecha_fin` (mismo campo que **Starting Override Date** en la UI): los agregados (Invoice QTY, paid en
    * líneas, etc.) solo incluyen facturas con `invoice.start_date` **estrictamente anterior** a esa fecha.
    * Esa fecha también enlaza la fila de override y se persiste al guardar ({@see SalvarOverridePayment}).
    *
    * @return array{items: array<int, array<string, mixed>>}
    */
   public function ListarItemsParaOverridePayment(
      ?string $company_id,
      ?string $project_id,
      ?string $fecha_fin
   ): array {
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $result = $invoiceItemRepo->ListarParaOverridePaymentConTotal(
         0,
         \PHP_INT_MAX,
         '',
         'item',
         'asc',
         (string) ($company_id ?? ''),
         (string) ($project_id ?? ''),
         $fecha_fin ?? ''
      );

      $raw = $result['data'];
      if ($raw === []) {
         return ['items' => []];
      }

      return ['items' => $this->buildOverridePaymentRowsFromAggregates($raw, $fecha_fin)];
   }

   /**
    * @param array<int, array<string, mixed>> $raw Filas agregadas por project_item desde el repositorio
    *
    * @return array<int, array<string, mixed>>
    */
   private function buildOverridePaymentRowsFromAggregates(array $raw, ?string $fecha_fin): array
   {
      $endDate = $this->parseDateMDY($fecha_fin);

      /** @var ProjectItemRepository $projectItemRepo */
      $projectItemRepo = $this->getDoctrine()->getRepository(ProjectItem::class);

      $piIds = array_map(static fn(array $r) => $r['project_item_id'], $raw);

      /** @var ProjectItem[] $projectItems */
      $projectItems = $projectItemRepo->findBy(['id' => $piIds]);
      $piById = [];
      foreach ($projectItems as $pi) {
         $piById[$pi->getId()] = $pi;
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
         if ($endDate !== null) {
            $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, $endDate);
         }
         if ($oid === null) {
            $oid = $overrideRepo->BuscarIdPorProjectItemYFechas($pid, null, null);
         }
         $mapOverrideId[$pid] = $oid;
      }
      $overrideIds = array_values(array_filter($mapOverrideId, static fn($id) => $id !== null));
      $overrideConHist = array_fill_keys($overrideHistRepo->IdsConHistorial($overrideIds), true);
      $unpaidOverrideConHist = array_fill_keys($unpaidOverrideHistRepo->IdsConHistorial($overrideIds), true);

      $paidQtyByPi = [];
      $unpaidExplicitByPi = [];
      if ($overrideIds !== []) {
         /** @var InvoiceItemOverridePayment[] $overrides */
         $overrides = $overrideRepo->findBy(['id' => $overrideIds]);
         foreach ($overrides as $ov) {
            $opi = $ov->getProjectItem();
            if ($opi === null) {
               continue;
            }
            if ($ov->getPaidQty() !== null) {
               $paidQtyByPi[$opi->getId()] = (float) $ov->getPaidQty();
            }
            if ($ov->getUnpaidQty() !== null) {
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
         if ($pi === null) {
            continue;
         }

         $itemEntity = $pi->getItem();
         if ($itemEntity === null) {
            continue;
         }

         if (!isset($histQtyCache[$pid])) {
            $histQtyCache[$pid] = $historyRepo->TieneHistorialCantidad($pid);
         }
         if (!isset($histPriceCache[$pid])) {
            $histPriceCache[$pid] = $historyRepo->TieneHistorialPrecio($pid);
         }

         $sumPaidLines = (float) ($r['sum_paid_lines'] ?? 0);
         $paidQtyDisplay = array_key_exists($pid, $paidQtyByPi) ? $paidQtyByPi[$pid] : $sumPaidLines;
         $sumQtyFinal = (float) ($r['sum_qty_final'] ?? 0);
         $contractQty = (float) ($pi->getQuantity() ?? 0);
         $priceContract = (float) ($pi->getPrice() ?? 0);

         $overrideId = $mapOverrideId[$pid] ?? null;
         if ($overrideId !== null && array_key_exists($pid, $unpaidExplicitByPi)) {
            $unpaidQty = $unpaidExplicitByPi[$pid];
         } else {
            $unpaidQty = max(0.0, $sumQtyFinal - $paidQtyDisplay);
         }
         $paidAmount = $paidQtyDisplay * $priceContract;

         $hasPaidOverrideHist = $overrideId !== null && isset($overrideConHist[$overrideId]);
         $hasUnpaidOverrideHist = $overrideId !== null && isset($unpaidOverrideConHist[$overrideId]);
         $unpaidQtyReadonly = $overrideId !== null
            && (array_key_exists($pid, $unpaidExplicitByPi) || $hasUnpaidOverrideHist);

         $codStr = $pi->getChangeOrderDate() !== null
            ? $pi->getChangeOrderDate()->format('m/d/Y')
            : '';

         $rows[] = [
            'project_item_id' => $pid,
            'item_id' => (int) ($itemEntity->getItemId() ?? 0),
            'invoice_item_override_payment_id' => $overrideId,
            'apply_retainage' => $pi->getApplyRetainage() ? 1 : 0,
            'bonded' => $pi->getBonded() ? 1 : 0,
            'bond' => $itemEntity->getBond() ? 1 : 0,
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
      /** @var InvoiceOverridePaymentRepository $headerRepo */
      $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
      $user = $this->getUser();
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
         if ($pi === null) {
            continue;
         }
         if ($project !== null && $pi->getProject()->getProjectId() !== $project->getProjectId()) {
            continue;
         }

         $projEntity = $pi->getProject();
         $header = $this->resolveInvoiceOverridePaymentHeader($em, $headerRepo, $projEntity, $endDate, $invoiceOverrideHeaderCache);

         $entity = $overrideRepo->findOneBy(
            ['projectItem' => $pi, 'invoiceOverridePayment' => $header],
            ['id' => 'ASC']
         );

         $oldPaid = null;
         if ($entity === null) {
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
            $oldPaid = (float) ($entity->getPaidQty() ?? 0);
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

      if ($itemsDecoded !== []) {
         $logMsg = $project !== null
            ? 'Override payment quantities saved for project #' . $project->getProjectNumber()
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
      int $project_item_id
   ): array {
      $projectIdTrim = trim($project_id);
      if ($projectIdTrim === '') {
         return ['success' => false, 'error' => 'Project required'];
      }
      $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
      if ($project === null) {
         return ['success' => false, 'error' => 'Project not found'];
      }
      $endDate = $this->parseDateMDY($fecha_fin);
      if ($endDate === null) {
         return ['success' => false, 'error' => 'Select invoice end date'];
      }
      $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
      if ($pi === null || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
         return ['success' => false, 'error' => 'Project item not found'];
      }
      $parent = $this->findPaymentOverrideForProjectItemEndDate($pi, $endDate);
      if ($parent === null || $parent->getId() === null) {
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
            'date' => $h->getCreatedAt() !== null ? $h->getCreatedAt()->format('m/d/Y') : '',
            'override_unpaid_qty' => $nv !== null && $nv !== '' ? (float) $nv : null,
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
      ?int $history_id = null
   ): array {
      $projectIdTrim = trim($project_id);
      if ($projectIdTrim === '') {
         return ['success' => false, 'error' => 'Project required'];
      }
      $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
      if ($project === null) {
         return ['success' => false, 'error' => 'Project not found'];
      }

      $endDate = $this->parseDateMDY($fecha_fin);
      if ($endDate === null) {
         return ['success' => false, 'error' => 'Select invoice end date'];
      }

      $plain = trim(strip_tags($notesHtml ?? ''));
      if ($plain === '') {
         return ['success' => false, 'error' => 'The note cannot be empty'];
      }

      if ($override_unpaid_qty_raw === null || $override_unpaid_qty_raw === '') {
         return ['success' => false, 'error' => 'Override unpaid qty is required'];
      }
      $unpaidQtyNew = (float) $override_unpaid_qty_raw;
      if ($unpaidQtyNew < 0) {
         return ['success' => false, 'error' => 'Override unpaid qty must be >= 0'];
      }

      $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
      if ($pi === null || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
         return ['success' => false, 'error' => 'Project item not found'];
      }

      $em = $this->getDoctrine()->getManager();
      /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
      $histRepo = $this->getDoctrine()->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
      $user = $this->getUser();

      if ($history_id !== null && $history_id > 0) {
         $hist = $histRepo->find($history_id);
         if (!$hist instanceof InvoiceItemOverridePaymentUnpaidQtyHistory) {
            return ['success' => false, 'error' => 'History entry not found'];
         }
         $parent = $hist->getInvoiceItemOverridePayment();
         if ($parent === null || $parent->getProjectItem()?->getId() !== $pi->getId()) {
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

         $logMsg = 'Unpaid qty override note updated for project #' . $project->getProjectNumber();
         $this->SalvarLog('Update', 'Override Payment', $logMsg);

         return [
            'success' => true,
            'invoice_item_override_payment_id' => (int) $parent->getId(),
            'note' => [
               'id' => (int) $hist->getId(),
               'date' => $hist->getCreatedAt() !== null ? $hist->getCreatedAt()->format('m/d/Y') : '',
               'override_unpaid_qty' => $unpaidQtyNew,
            ],
         ];
      }

      $parent = $this->findPaymentOverrideForProjectItemEndDate($pi, $endDate);
      if ($parent === null) {
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

      if ($parent->getUnpaidQty() !== null) {
         $oldStr = (string) $parent->getUnpaidQty();
      } else {
         $oldStr = (string) $this->baselineUnpaidFromInvoiceLinesNonBond($pi, $parent, $endDate);
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

      $logMsg = 'Unpaid qty override note saved for project #' . $project->getProjectNumber();
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
      int $history_id
   ): array {
      $projectIdTrim = trim($project_id);
      if ($projectIdTrim === '' || $history_id <= 0) {
         return ['success' => false, 'error' => 'Invalid request'];
      }
      $project = $this->getDoctrine()->getRepository(Project::class)->find((int) $projectIdTrim);
      if ($project === null) {
         return ['success' => false, 'error' => 'Project not found'];
      }
      $pi = $this->getDoctrine()->getRepository(ProjectItem::class)->find($project_item_id);
      if ($pi === null || $pi->getProject()->getProjectId() !== $project->getProjectId()) {
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
      if ($parent === null || $parent->getProjectItem()?->getId() !== $pi->getId()) {
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

      $this->SalvarLog('Update', 'Override Payment', 'Unpaid qty override note deleted for project #' . $project->getProjectNumber());

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
         $old_value = $old_value_raw !== null && $old_value_raw !== '' ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
         $new_value = $new_value_raw !== null && $new_value_raw !== '' ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
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
         $old_value = $old_value_raw !== null && $old_value_raw !== '' ? number_format((float) $old_value_raw, 2, '.', ',') : '—';
         $new_value = $new_value_raw !== null && $new_value_raw !== '' ? number_format((float) $new_value_raw, 2, '.', ',') : '—';
         $notaTxt = $value->getNote();
         $notaResumen = '';
         if ($notaTxt !== null && trim(strip_tags($notaTxt)) !== '') {
            $strip = trim(strip_tags($notaTxt));
            $notaResumen = mb_strlen($strip) > 120 ? mb_substr($strip, 0, 120) . '…' : $strip;
         }
         $mensaje = "{$fecha} Updated unpaid qty from \"{$old_value}\" to \"{$new_value}\" by \"{$user_name}\"";
         if ($notaResumen !== '') {
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
         $pi = $override !== null ? $override->getProjectItem() : null;
         $item = $pi !== null ? $pi->getItem() : null;
         $user = $h->getUser();
         $userName = $user !== null ? $user->getNombreCompleto() : 'Unknown';
         $oldRaw = $h->getOldValue();
         $newRaw = $h->getNewValue();
         $oldQty = $oldRaw !== null && $oldRaw !== '' ? number_format((float) $oldRaw, 2, '.', ',') : '—';
         $newQty = $newRaw !== null && $newRaw !== '' ? number_format((float) $newRaw, 2, '.', ',') : '—';
         $created = $h->getCreatedAt();
         $out[] = [
            'id' => $h->getId(),
            'item_description' => $item !== null ? (string) ($item->getDescription() ?? '') : '',
            'old_qty' => $oldQty,
            'new_qty' => $newQty,
            'user_name' => $userName,
            'created_at' => $created !== null ? $created->format('m/d/Y H:i') : '',
            'posicion' => $key,
         ];
      }

      return $out;
   }

   private function isUniqueConstraintViolation(\Throwable $e): bool
   {
      $cur = $e;
      for ($i = 0; $i < 6 && $cur !== null; $i++) {
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
      array &$headerCache
   ): InvoiceOverridePayment {
      $key = (string) $projEntity->getProjectId() . '|' . $endDate->format('Y-m-d');
      if (isset($headerCache[$key])) {
         return $headerCache[$key];
      }
      $header = $headerRepo->findOneByProjectAndDate((int) $projEntity->getProjectId(), $endDate);
      if ($header === null) {
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
      \DateTimeInterface $endDate
   ): ?InvoiceItemOverridePayment {
      /** @var InvoiceOverridePaymentRepository $headerRepo */
      $headerRepo = $this->getDoctrine()->getRepository(InvoiceOverridePayment::class);
      /** @var InvoiceItemOverridePaymentRepository $repo */
      $repo = $this->getDoctrine()->getRepository(InvoiceItemOverridePayment::class);
      $projectId = (int) $pi->getProject()->getProjectId();
      foreach ([$endDate, null] as $d) {
         $header = $headerRepo->findOneByProjectAndDate($projectId, $d);
         if ($header === null) {
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
      \Doctrine\ORM\EntityManagerInterface $em
   ): void {
      $pid = $payment->getId();
      if ($pid === null) {
         return;
      }
      /** @var InvoiceItemOverridePaymentUnpaidQtyHistoryRepository $histRepo */
      $histRepo = $em->getRepository(InvoiceItemOverridePaymentUnpaidQtyHistory::class);
      $latest = $histRepo->findLatestByOverrideId((int) $pid);
      if ($latest === null) {
         $payment->setUnpaidQty(null);
         $payment->setUpdatedAt(new \DateTime());

         return;
      }
      $nv = $latest->getNewValue();
      $payment->setUnpaidQty($nv !== null && $nv !== '' ? (float) $nv : null);
      $payment->setUpdatedAt(new \DateTime());
   }

   /**
    * Paid “de sistema” antes del override: suma de paid_qty en líneas de factura (sin ítems bond),
    * misma base que el listado Override Payment (mismo corte por start_date).
    */
   private function baselinePaidFromInvoiceLinesNonBond(ProjectItem $pi, ?\DateTimeInterface $startOverride = null): float
   {
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $ymd = $startOverride !== null ? $startOverride->format('Y-m-d') : null;
      $a = $invoiceItemRepo->aggregateNonBondInvoiceQtyPaidForProjectItem((int) $pi->getId(), $ymd);

      return (float) ($a['sum_paid_lines'] ?? 0);
   }

   /**
    * Unpaid calculado antes de override explícito: max(0, invoice qty final − paid efectivo).
    * Si ya hay paid en la fila de override, sustituye a la suma de líneas (como en la grilla).
    */
   private function baselineUnpaidFromInvoiceLinesNonBond(ProjectItem $pi, ?InvoiceItemOverridePayment $row, ?\DateTimeInterface $startOverride = null): float
   {
      /** @var InvoiceItemRepository $invoiceItemRepo */
      $invoiceItemRepo = $this->getDoctrine()->getRepository(InvoiceItem::class);
      $ymd = $startOverride !== null ? $startOverride->format('Y-m-d') : null;
      $a = $invoiceItemRepo->aggregateNonBondInvoiceQtyPaidForProjectItem((int) $pi->getId(), $ymd);
      $sumQtyFinal = (float) ($a['sum_qty_final'] ?? 0);
      $sumPaidLines = (float) ($a['sum_paid_lines'] ?? 0);
      $paidDisplay = $sumPaidLines;
      if ($row !== null && $row->getPaidQty() !== null) {
         $paidDisplay = (float) $row->getPaidQty();
      }

      return max(0.0, $sumQtyFinal - $paidDisplay);
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
