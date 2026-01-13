<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Invoice::class);
   }

   /**
    * ListarInvoicesDeProject: Lista los invoices de un project
    *
    * @return Invoice[]
    */
   public function ListarInvoicesDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('i')
         ->leftJoin('i.project', 'p')
         ->andWhere('p.projectId = :project_id')
         ->setParameter('project_id', $project_id);

      $consulta->orderBy('i.createdAt', "DESC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarInvoices: Lista los invoices con filtros y paginación
    *
    * @return Invoice[]
    */
   public function ListarInvoices(
      int $start,
      int $limit,
      ?string $sSearch,
      string $iSortCol_0,
      string $sSortDir_0,
      string $company_id = '',
      string $project_id = '',
      string $fecha_inicial = '',
      string $fecha_fin = '',
      string $paid = ''
   ): array {

      $qb = $this->createQueryBuilder('i')
         ->leftJoin('i.project', 'p')
         ->leftJoin('p.company', 'c');

      // Filtros de búsqueda
      if (!empty($sSearch)) {
         $qb->andWhere('i.number LIKE :search OR i.notes LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search OR p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search OR p.poCG LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if (!empty($company_id)) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if (!empty($project_id)) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if (!empty($fecha_inicial)) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if (!empty($fecha_fin)) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      if ($paid !== '') {
         $qb->andWhere('i.paid = :paid')
            ->setParameter('paid', $paid);
      }

      // Ordenar por la columna seleccionada
      $qb->orderBy("i.$iSortCol_0", $sSortDir_0);

      // Limitar los resultados con paginación
      if ($limit > 0) {
         $qb->setMaxResults($limit)
            ->setFirstResult($start);
      }

      return $qb->getQuery()->getResult();
   }

   /**
    * TotalInvoices: Total de invoices con filtros aplicados.
    *
    * @return int
    */
   public function TotalInvoices(?string $sSearch = '', string $company_id = '', string $project_id = '', string $fecha_inicial = '', string $fecha_fin = '', string $paid = ''): int
   {
      $qb = $this->createQueryBuilder('i')
         ->select('COUNT(i.invoiceId)')
         ->leftJoin('i.project', 'p')
         ->leftJoin('p.company', 'c');

      // Filtros de búsqueda
      if (!empty($sSearch)) {
         $qb->andWhere('i.number LIKE :search OR i.notes LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search OR p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR p.poNumber LIKE :search OR p.poCG LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if (!empty($company_id)) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if (!empty($project_id)) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if (!empty($fecha_inicial)) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if (!empty($fecha_fin)) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      if ($paid !== '') {
         $qb->andWhere('i.paid = :paid')
            ->setParameter('paid', $paid);
      }

      return (int) $qb->getQuery()->getSingleScalarResult();
   }


   /**
    * ListarInvoicesConTotal: Lista y cuenta aplicando los mismos filtros.
    *
    */
   public function ListarInvoicesConTotal(
      int $start,
      int $limit,
      ?string $sSearch = null,
      string $sortField = 'startDate',
      string $sortDir = 'DESC',
      string $company_id = '',
      string $project_id = '',
      string $fecha_inicial = '',
      string $fecha_fin = '',
      string $paid = ''
   ): array {

      // Whitelist de campos ordenables
      $sortable = [
         'id' => 'i.invoiceId',
         'number' => 'i.number',
         'company' => 'c.name',
         'projectNumber' => 'p.projectNumber',
         'project' => 'p.name',
         'startDate' => 'i.startDate',
         'endDate' => 'i.endDate',
         'total' => 'i.number',
         'notes' => 'i.notes',
         'paid' => 'i.paid',
         'createdAt' => 'i.createdAt',
      ];
      $orderBy = $sortable[$sortField] ?? 'i.startDate';
      $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con JOIN y filtros
      $baseQb = $this->createQueryBuilder('i')
         ->leftJoin('i.project', 'p')
         ->leftJoin('p.company', 'c');

      // Filtros de búsqueda
      if (!empty($sSearch)) {
         $baseQb->andWhere('i.number LIKE :search OR i.notes LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search OR
             p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR
              p.poNumber LIKE :search OR p.poCG LIKE :search OR c.name LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if (!empty($company_id)) {
         $baseQb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if (!empty($project_id)) {
         $baseQb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if (!empty($fecha_inicial)) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $baseQb->andWhere('i.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if (!empty($fecha_fin)) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $baseQb->andWhere('i.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      if ($paid !== '') {
         $baseQb->andWhere('i.paid = :paid')
            ->setParameter('paid', $paid);
      }

      // ---- Datos (con paginación y orden) ----
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start);

      if ($limit > 0) {
         $dataQb->setMaxResults($limit);
      }

      $data = $dataQb->getQuery()->getResult();

      // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(i.invoiceId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,
         'total' => $total, // total con el MISMO filtro aplicado
      ];
   }

   /**
    * ListarInvoicesRangoFecha: Lista los invoices dentro de un rango de fechas con filtros.
    *
    * @return Invoice[]
    */
   public function ListarInvoicesRangoFecha(string $company_id = '', string $project_id = '', string $fecha_inicial = '', string $fecha_fin = '', string $status = ''): array
   {
      $qb = $this->createQueryBuilder('i')
         ->leftJoin('i.project', 'p')
         ->leftJoin('p.company', 'c');

      // Filtros adicionales
      if (!empty($company_id)) {
         $qb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if (!empty($project_id)) {
         $qb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if (!empty($status)) {
         $qb->andWhere('p.status = :status')
            ->setParameter('status', $status);
      }

      if (!empty($fecha_inicial)) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $qb->andWhere('i.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if (!empty($fecha_fin)) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $qb->andWhere('i.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      // Ordenar por la fecha de inicio
      $qb->orderBy('i.startDate', 'ASC');

      return $qb->getQuery()->getResult();
   }

   /**
    * ListarInvoicesParaPaymentsConTotal: Lista y cuenta aplicando los mismos filtros.
    *
    */
   public function ListarInvoicesParaPaymentsConTotal(
      int $start,
      int $limit,
      ?string $sSearch = null,
      string $sortField = 'startDate',
      string $sortDir = 'DESC',
      string $company_id = '',
      string $project_id = '',
      string $fecha_inicial = '',
      string $fecha_fin = '',
      string $paid = ''
   ): array {

      // Whitelist de campos ordenables
      $sortable = [
         'id' => 'i.invoiceId',
         'number' => 'i.number',
         'company' => 'c.name',
         'projectNumber' => 'p.projectNumber',
         'project' => 'p.name',
         'startDate' => 'i.startDate',
         'endDate' => 'i.endDate',
         'total' => 'i.number',
         'notes' => 'i.notes',
         'paid' => 'i.paid',
         'createdAt' => 'i.createdAt',
      ];
      $orderBy = $sortable[$sortField] ?? 'i.startDate';
      $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con JOIN y filtros
      $baseQb = $this->createQueryBuilder('i')
         ->leftJoin('i.project', 'p')
         ->leftJoin('p.company', 'c');

      // Filtros de búsqueda
      if (!empty($sSearch)) {
         $baseQb->andWhere('i.number LIKE :search OR i.notes LIKE :search OR p.invoiceContact LIKE :search OR p.owner LIKE :search OR
             p.manager LIKE :search OR p.county LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR
              p.poNumber LIKE :search OR p.poCG LIKE :search OR c.name LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Filtros adicionales
      if (!empty($company_id)) {
         $baseQb->andWhere('c.companyId = :company_id')
            ->setParameter('company_id', $company_id);
      }

      if (!empty($project_id)) {
         $baseQb->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      if (!empty($fecha_inicial)) {
         $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
         $baseQb->andWhere('i.startDate >= :fecha_inicial')
            ->setParameter('fecha_inicial', $fecha_inicial);
      }

      if (!empty($fecha_fin)) {
         $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
         $baseQb->andWhere('i.endDate <= :fecha_final')
            ->setParameter('fecha_final', $fecha_fin);
      }

      if ($paid !== '') {
         $baseQb->andWhere('i.paid = :paid')
            ->setParameter('paid', $paid);
      }

      // ---- Datos (con paginación y orden) ----
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start);

      if ($limit > 0) {
         $dataQb->setMaxResults($limit);
      }

      $data = $dataQb->getQuery()->getResult();

      // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(i.invoiceId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,
         'total' => $total, // total con el MISMO filtro aplicado
      ];
   }

   /**
    * ObtenerTotalPagadoConRetainage: Suma LO PAGADO de items con retainage
    */
   public function ObtenerTotalPagadoConRetainage($project_id)
   {
      try {
         return (float) $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(ii.paidAmount)') // Usamos paidAmount
            ->from('App\Entity\InvoiceItem', 'ii')
            ->join('ii.invoice', 'i')
            ->join('ii.projectItem', 'pi')

            ->where('i.project = :project_id')
            ->andWhere('pi.applyRetainage = 1') // Solo items que llevan retainage

            ->setParameter('project_id', $project_id)
            ->getQuery()
            ->getSingleScalarResult();
      } catch (\Exception $e) {
         return 0.00;
      }
   }
   /**
    * ObtenerTotalPagadoAnterior: Suma LO PAGADO de facturas ANTERIORES a la actual
    */
   public function ObtenerTotalPagadoAnterior($project_id, $current_invoice_date, $current_invoice_id)
   {
      try {
         // Buscamos facturas con fecha MENOR, o misma fecha pero ID MENOR
         return (float) $this->getEntityManager()->createQueryBuilder()
            ->select('SUM(ii.paidAmount)')
            ->from('App\Entity\InvoiceItem', 'ii')
            ->join('ii.invoice', 'i')
            ->join('ii.projectItem', 'pi')
            ->where('i.project = :project_id')
            ->andWhere('pi.applyRetainage = 1')
            ->andWhere('i.startDate < :date OR (i.startDate = :date AND i.invoiceId < :id)')
            ->setParameter('project_id', $project_id)
            ->setParameter('date', $current_invoice_date)
            ->setParameter('id', $current_invoice_id)
            ->getQuery()
            ->getSingleScalarResult();
      } catch (\Exception $e) {
         return 0.00;
      }
   }
}
