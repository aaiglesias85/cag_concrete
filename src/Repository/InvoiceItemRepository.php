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
}
