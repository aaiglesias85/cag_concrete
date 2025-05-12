<?php

namespace App\Repository;

use App\Entity\InvoiceItem;
use Doctrine\ORM\EntityRepository;

class InvoiceItemRepository extends EntityRepository
{
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
            ->orderBy('i_i.id', 'ASC');

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
    public function TotalPreviousQuantity(int $project_item_id = null): float
    {
        $qb = $this->createQueryBuilder('i_i')
            ->select('SUM(i_i.quantity)')
            ->leftJoin('i_i.projectItem', 'p_i');

        if ($project_item_id) {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * TotalInvoice: Obtiene el total de las facturas de los items.
     *
     * @return float
     */
    public function TotalInvoice(?string $invoice_id = null, ?string $company_id = null, ?string $project_id = null, ?string $fecha_inicial = null, ?string $fecha_fin = null, ?string $item_id = null, ?string $status = null): float {
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