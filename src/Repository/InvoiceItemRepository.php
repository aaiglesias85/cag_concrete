<?php

namespace App\Repository;

use App\Entity\InvoiceItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, InvoiceItem::class);
   }
   /**
    * ListarItems: Lista los items asociados a una factura.
    *
    * @param int $invoice_id El ID de la factura
    *
    * @return InvoiceItem[]
    */
   public function ListarItems(int $invoice_id): array
   {
      $qb = $this->createQueryBuilder('i_i')
         ->leftJoin('i_i.invoice', 'i')
         ->orderBy('i_i.id', 'ASC');

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * ListarInvoicesDeItem: Lista las facturas asociadas a un item de proyecto.
    *
    * @param int $project_item_id El ID del item de proyecto
    *
    * @return InvoiceItem[]
    */
   public function ListarInvoicesDeItem(int $project_item_id): array
   {
      $qb = $this->createQueryBuilder('i_i')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->orderBy('i.startDate', 'ASC')
         ->addOrderBy('i.invoiceId', 'ASC');

      if ($project_item_id) {
         $qb->andWhere('p_i.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * Número de líneas invoice_item del project_item (todas las facturas).
    */
   public function CountInvoiceLinesForProjectItem(int $project_item_id): int
   {
      $n = $this->createQueryBuilder('i_i')
         ->select('COUNT(i_i.id)')
         ->join('i_i.projectItem', 'p_i')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id)
         ->getQuery()
         ->getSingleScalarResult();

      return (int) $n;
   }

   /**
    * TotalPreviousQuantity: Obtiene el total de cantidad de items.
    *
    * @param int $project_item_id El ID del item de proyecto
    *
    * @return float
    */
   public function TotalPreviousQuantity(int $project_item_id = null, int $invoice_id = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM(i_i.quantity)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i');

      if ($project_item_id) {
         $qb->andWhere('p_i.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * SumBondedInvoiceItems: Suma de (quantity + quantity_brought_forward) * price
    * para los ítems del invoice cuyo project_item tiene bonded = true.
    * Es el "SUM_BONDED_INVOICES" usado para calcular X (Bond Quantity solicitado) por invoice.
    *
    * @param int $invoice_id
    * @return float
    */
   public function SumBondedInvoiceItems(int $invoice_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM((i_i.quantity + COALESCE(i_i.quantityBroughtForward, 0)) * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->andWhere('p_i.bonded = :bonded')
         ->andWhere('i.invoiceId = :invoice_id')
         ->setParameter('bonded', true)
         ->setParameter('invoice_id', $invoice_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * SumBondPaidQtyForInvoice: Suma de paid_qty de los ítems Bond del invoice.
    * Usado para el consumo acumulado real: Σ bon_quantity − Σ paid_qty (Bond) en invoices anteriores.
    *
    * @param int $invoice_id
    * @return float
    */
   public function SumBondPaidQtyForInvoice(int $invoice_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.paidQty), 0)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('p_i.item', 'item')
         ->leftJoin('i_i.invoice', 'i')
         ->andWhere('item.bond = :bond')
         ->andWhere('i.invoiceId = :invoice_id')
         ->setParameter('bond', true)
         ->setParameter('invoice_id', $invoice_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * SumBondPaidQtyForInvoicesBeforeOrOnDate: Suma de paid_qty (Bond) de invoices del proyecto
    * con start_date <= la fecha dada. Para cálculo de disponible: available = 1 - (Σ bon_quantity − Σ bond paid_qty).
    * La fecha puede venir en formato m/d/Y.
    *
    * @param int|string $project_id
    * @param string $start_date_str fecha en m/d/Y
    * @return float
    */
   public function SumBondPaidQtyForInvoicesBeforeOrOnDate($project_id, string $start_date_str): float
   {
      $date = \DateTime::createFromFormat('m/d/Y', trim($start_date_str));
      if (!$date) {
         return 0.0;
      }
      $dateStr = $date->format('Y-m-d');

      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.paidQty), 0)')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('i.project', 'p')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('p_i.item', 'item')
         ->andWhere('item.bond = :bond')
         ->andWhere('p.projectId = :project_id')
         ->andWhere('i.startDate <= :date')
         ->setParameter('bond', true)
         ->setParameter('project_id', $project_id)
         ->setParameter('date', $dateStr);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * TotalPreviousAmount: Obtiene el total de cantidad de items por precio.
    *
    * @param int $project_item_id El ID del item de proyecto
    *
    * @return float
    */
   public function TotalPreviousAmount(int $project_item_id = null, int $invoice_id = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM(i_i.quantity * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i');

      if ($project_item_id) {
         $qb->andWhere('p_i.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * TotalInvoice: Obtiene el total de las facturas de los items.
    *
    * @return float
    */
   public function TotalInvoice(?string $invoice_id = null, ?string $company_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $item_id = null, ?string $status = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM(i_i.quantity * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p.company', 'c');

      if ($item_id) {
         $qb->andWhere('i_i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :inicio')
            ->setParameter('inicio', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fin')
            ->setParameter('fin', $fecha_fin);
      }

      if ($status !== null) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * TotalInvoiceBroughtForward: Obtiene el total de las facturas de los items.
    *
    * @return float
    */
   public function TotalInvoiceBroughtForward(?string $invoice_id = null, ?string $company_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $item_id = null, ?string $status = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM((i_i.quantity + i_i.quantityBroughtForward) * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p.company', 'c');

      if ($item_id) {
         $qb->andWhere('i_i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :inicio')
            ->setParameter('inicio', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fin')
            ->setParameter('fin', $fecha_fin);
      }

      if ($status !== null) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * TotalInvoiceFinalAmountThisPeriod: Obtiene la suma de Final Amount This Period ((quantity + quantityBroughtForward) * price) de los items de invoice
    * Excluye los items marcados como change order
    *
    * @return float
    */
   public function TotalInvoiceFinalAmountThisPeriod(?string $invoice_id = null, ?string $company_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $item_id = null, ?string $status = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM((i_i.quantity + COALESCE(i_i.quantityBroughtForward, 0)) * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p.company', 'c');

      if ($item_id) {
         $qb->andWhere('i_i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :inicio')
            ->setParameter('inicio', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fin')
            ->setParameter('fin', $fecha_fin);
      }

      if ($status !== null) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      // Excluir items marcados como change order
      //$qb->andWhere('p_i.changeOrder IS NULL OR p_i.changeOrder = false');

      return (float) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * TotalInvoicePaidAmount: Obtiene la suma de Paid Amount (paid_amount) de los items de invoice
    *
    * @return float
    */
   public function TotalInvoicePaidAmount(?string $invoice_id = null, ?string $company_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $item_id = null, ?string $status = null): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM(i_i.paidAmount)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('p_i.project', 'p')
         ->leftJoin('p.company', 'c');

      if ($item_id) {
         $qb->andWhere('i_i.itemId = :item_id')
            ->setParameter('item_id', $item_id);
      }

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      if ($project_id) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if ($company_id) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if ($fecha_inicial) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :inicio')
            ->setParameter('inicio', $fecha_inicial);
      }

      if ($fecha_fin) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fin')
            ->setParameter('fin', $fecha_fin);
      }

      if ($status !== null) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * TotalInvoiceQuantityByProjectItem: Obtiene la suma de quantity de los invoice items de un project_item
    *
    * @param int $project_item_id El ID del item de proyecto
    * @return float
    */
   public function TotalInvoiceQuantityByProjectItem(int $project_item_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.quantity), 0)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) $result;
   }

   /**
    * TotalInvoiceAmountByProjectItem: Obtiene la suma de amount (quantity * price) de los invoice items de un project_item
    *
    * @param int $project_item_id El ID del item de proyecto
    * @return float
    */
   public function TotalInvoiceAmountByProjectItem(int $project_item_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.quantity * i_i.price), 0)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) $result;
   }

   /**
    * TotalInvoicePaidQtyByProjectItem: Obtiene la suma de paid_qty de los invoice items de un project_item
    *
    * @param int $project_item_id El ID del item de proyecto
    * @return float
    */
   public function TotalInvoicePaidQtyByProjectItem(int $project_item_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.paidQty), 0)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) $result;
   }

   /**
    * Suma de cantidades y paid de líneas de factura para un project_item, excluyendo ítems bond.
    * Misma base que {@see ListarParaOverridePaymentConTotal} sin filtro de fecha.
    *
    * @return array{sum_qty_final: float, sum_paid_lines: float}
    */
   public function aggregateNonBondInvoiceQtyPaidForProjectItem(int $projectItemId): array
   {
      $conn = $this->getEntityManager()->getConnection();
      $sql = '
         SELECT
            COALESCE(SUM(ii.quantity + COALESCE(ii.quantity_brought_forward, 0)), 0) AS sum_qty_final,
            COALESCE(SUM(ii.paid_qty), 0) AS sum_paid_lines
         FROM invoice_item ii
         INNER JOIN project_item pi ON ii.project_item_id = pi.id
         INNER JOIN item it ON pi.item_id = it.item_id
         WHERE pi.id = :pid
           AND (it.bond IS NULL OR it.bond = 0)
      ';
      $row = $conn->executeQuery($sql, ['pid' => $projectItemId])->fetchAssociative() ?: [];

      return [
         'sum_qty_final' => (float) ($row['sum_qty_final'] ?? 0),
         'sum_paid_lines' => (float) ($row['sum_paid_lines'] ?? 0),
      ];
   }

   /**
    * TotalInvoicePaidAmountByProjectItem: Obtiene la suma de paid_amount de los invoice items de un project_item
    *
    * @param int $project_item_id El ID del item de proyecto
    * @return float
    */
   public function TotalInvoicePaidAmountByProjectItem(int $project_item_id): float
   {
      $qb = $this->createQueryBuilder('i_i')
         ->select('COALESCE(SUM(i_i.paidAmount), 0)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->andWhere('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) $result;
   }

   /**
    * BuscarItem: Busca un item por su factura y item de proyecto.
    *
    * @param int $invoice_id El ID de la factura
    * @param int $project_item_id El ID del item de proyecto
    *
    * @return InvoiceItem|null
    */
   public function BuscarItem(int $invoice_id, int $project_item_id): ?InvoiceItem
   {
      $qb = $this->createQueryBuilder('i_i')
         ->leftJoin('i_i.invoice', 'i')
         ->leftJoin('i_i.projectItem', 'p_i');

      if ($invoice_id) {
         $qb->andWhere('i.invoiceId = :invoice_id')
            ->setParameter('invoice_id', $invoice_id);
      }

      if ($project_item_id) {
         $qb->andWhere('p_i.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id);
      }

      return $qb->getQuery()->getOneOrNullResult();
   }

   /**
    * Calcula el total del invoice (Final Amount This Period) SOLO de los items con Retainage activo
    */
   public function TotalInvoiceFinalAmountThisPeriodRetainageOnly($invoice_id)
   {
      $em = $this->getEntityManager();
      $connection = $em->getConnection();

      $sql = "
         SELECT SUM((ii.quantity + ii.quantity_brought_forward) * ii.price)
         FROM invoice_item ii
         JOIN project_item pi ON ii.project_item_id = pi.id
         WHERE ii.invoice_id = :invoice_id
         AND pi.apply_retainage = 1
      ";

      $stmt = $connection->prepare($sql);
      $resultado = $stmt->executeQuery(['invoice_id' => $invoice_id])->fetchOne();

      return $resultado ? (float)$resultado : 0;
   }

   /**
    * TotalInvoiceFinalAmountThisPeriodBonedOnly: Obtiene la suma de Final Amount This Period 
    * de items BONED en el invoice especificado.
    * 
    * IMPORTANTE: Solo calcula para el invoice actual. Para invoices nuevos (sin invoice_id), retorna 0
    * porque los items aún no están guardados en la BD.
    * 
    * @param int|null $invoice_id El ID del invoice (requerido para calcular)
    * @param int|null $project_id El ID del proyecto (no se usa si invoice_id está presente)
    * @param string|null $fecha_inicial Fecha inicial (no se usa si invoice_id está presente)
    * @param string|null $fecha_fin Fecha final (no se usa si invoice_id está presente)
    * @return float
    */
   public function TotalInvoiceFinalAmountThisPeriodBonedOnly(?int $invoice_id = null, ?int $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null): float
   {
      // Si no hay invoice_id, retornar 0 (invoice nuevo, items aún no guardados)
      if (!$invoice_id) {
         return 0.0;
      }

      $qb = $this->createQueryBuilder('i_i')
         ->select('SUM((i_i.quantity + COALESCE(i_i.quantityBroughtForward, 0)) * i_i.price)')
         ->leftJoin('i_i.projectItem', 'p_i')
         ->leftJoin('i_i.invoice', 'i')
         ->andWhere('p_i.bonded = 1') // Solo items con bonded = true
         ->andWhere('i.invoiceId = :invoice_id') // Solo el invoice actual
         ->setParameter('invoice_id', $invoice_id);

      $result = $qb->getQuery()->getSingleScalarResult();
      return (float) ($result ?? 0);
   }

   /**
    * ListarParaOverridePaymentConTotal: invoice_item agrupados por project_item_id.
    * Filtro opcional por fecha (solo si `fecha_fin` no está vacío): invoice.end_date <= fecha_fin (inclusive), m/d/Y.
    * Cadena vacía = todos los invoices del proyecto en los agregados (p. ej. listado Override Payment).
    *
    * @return array{data: array<int, array{project_item_id: int, sum_qty_final: float, sum_paid_lines: float, sum_qty_completed: float, sum_amount: float, sum_total_amount: float}>, total: int}
    */
   public function ListarParaOverridePaymentConTotal(
      int $start,
      int $limit,
      ?string $sSearch,
      string $sortField,
      string $sortDir,
      string $company_id,
      string $project_id,
      string $fecha_fin
   ): array {
      $fechaFinYmd = '';
      if (!empty($fecha_fin)) {
         $d = \DateTime::createFromFormat('m/d/Y', $fecha_fin);
         if ($d !== false) {
            $fechaFinYmd = $d->format('Y-m-d');
         }
      }
      $sortable = [
         'item' => 'MAX(it.name)',
         'unit' => 'MAX(u.description)',
         'contract_qty' => 'MAX(pi.quantity)',
         'price' => 'MAX(pi.price)',
         'quantity' => 'sum_qty_final',
         'paid_qty' => 'sum_paid_lines',
         'unpaid_qty' => 'unpaid_sort',
      ];
      $orderCol = $sortable[$sortField] ?? 'MAX(it.name)';
      $dir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

      $conn = $this->getEntityManager()->getConnection();

      $searchTrim = $sSearch !== null ? trim($sSearch) : '';
      $searchClause = '';
      $params = [];
      if ($searchTrim !== '') {
         // Mismo criterio amplio que ListarInvoicesParaPaymentsConTotal (invoice + proyecto + company) + catálogo ítem/unidad.
         $searchClause = ' AND (
            it.name LIKE :search_like OR it.description LIKE :search_like OR u.description LIKE :search_like
            OR i.number LIKE :search_like OR i.notes LIKE :search_like
            OR p.invoice_contact LIKE :search_like OR p.owner LIKE :search_like OR p.manager LIKE :search_like
            OR p.county LIKE :search_like OR p.project_number LIKE :search_like OR p.name LIKE :search_like OR p.description LIKE :search_like
            OR p.po_number LIKE :search_like OR p.po_cg LIKE :search_like
            OR c.name LIKE :search_like
         ) ';
         $params['search_like'] = '%' . $searchTrim . '%';
      }

      $dateClause = '';
      if ($fechaFinYmd !== '') {
         $dateClause .= ' AND i.end_date <= :fecha_final';
         $params['fecha_final'] = $fechaFinYmd;
      }

      $companyClause = '';
      if ($company_id !== '') {
         $companyClause = ' AND p.company_id = :company_id';
         $params['company_id'] = (int) $company_id;
      }

      $projectClause = '';
      if ($project_id !== '') {
         $projectClause = ' AND i.project_id = :project_id';
         $params['project_id'] = (int) $project_id;
      }

      $fromWhere = "
         FROM invoice_item ii
         INNER JOIN invoice i ON ii.invoice_id = i.invoice_id
         INNER JOIN project p ON i.project_id = p.project_id
         LEFT JOIN company c ON p.company_id = c.company_id
         INNER JOIN project_item pi ON ii.project_item_id = pi.id
         INNER JOIN item it ON pi.item_id = it.item_id
         LEFT JOIN unit u ON u.unit_id = it.unit_id
         WHERE (it.bond IS NULL OR it.bond = 0)
           $companyClause
           $projectClause
           $dateClause
           $searchClause
      ";

      $selectAgg = "
         pi.id AS project_item_id,
         SUM(ii.quantity + COALESCE(ii.quantity_brought_forward, 0)) AS sum_qty_final,
         SUM(ii.paid_qty) AS sum_paid_lines,
         SUM(ii.quantity + COALESCE(ii.unpaid_from_previous, 0) + COALESCE(ii.quantity_from_previous, 0)) AS sum_qty_completed,
         SUM((ii.quantity + COALESCE(ii.quantity_brought_forward, 0)) * ii.price) AS sum_amount,
         SUM((ii.quantity + COALESCE(ii.unpaid_from_previous, 0) + COALESCE(ii.quantity_from_previous, 0)) * ii.price) AS sum_total_amount,
         GREATEST(0,
           SUM(ii.quantity + COALESCE(ii.quantity_brought_forward, 0)) - SUM(ii.paid_qty)
         ) AS unpaid_sort
      ";

      $sqlCount = "SELECT COUNT(*) AS cnt FROM (SELECT pi.id AS pid $fromWhere GROUP BY pi.id) t";
      $total = (int) $conn->executeQuery($sqlCount, $params)->fetchOne();

      $sqlData = "SELECT $selectAgg $fromWhere GROUP BY pi.id ORDER BY $orderCol $dir, pi.id ASC";
      if ($limit > 0 && $limit < PHP_INT_MAX) {
         $sqlData .= ' LIMIT ' . (int) $limit . ' OFFSET ' . (int) $start;
      }

      $rows = $conn->executeQuery($sqlData, $params)->fetchAllAssociative();

      $data = [];
      foreach ($rows as $r) {
         $r = array_change_key_case((array) $r, CASE_LOWER);
         $data[] = [
            'project_item_id' => (int) ($r['project_item_id'] ?? 0),
            'sum_qty_final' => (float) ($r['sum_qty_final'] ?? 0),
            'sum_paid_lines' => (float) ($r['sum_paid_lines'] ?? 0),
            'sum_qty_completed' => (float) ($r['sum_qty_completed'] ?? 0),
            'sum_amount' => (float) ($r['sum_amount'] ?? 0),
            'sum_total_amount' => (float) ($r['sum_total_amount'] ?? 0),
         ];
      }

      return [
         'data' => $data,
         'total' => $total,
      ];
   }
}
