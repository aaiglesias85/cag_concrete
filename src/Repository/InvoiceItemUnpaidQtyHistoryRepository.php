<?php

namespace App\Repository;

use App\Entity\InvoiceItemUnpaidQtyHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemUnpaidQtyHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemUnpaidQtyHistory::class);
    }

    /**
     * ListarHistorialDeItem: Lista el historial de cambios de unpaid qty de un InvoiceItem
     *
     * @param int $invoice_item_id
     * @return InvoiceItemUnpaidQtyHistory[]
     */
    public function ListarHistorialDeItem(int $invoice_item_id): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.invoiceItem', 'i_i')
            ->leftJoin('h.user', 'u')
            ->where('i_i.id = :invoice_item_id')
            ->setParameter('invoice_item_id', $invoice_item_id)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * TieneHistorial: Verifica si un InvoiceItem tiene historial de cambios de unpaid qty
     *
     * @param int $invoice_item_id
     * @return bool
     */
    public function TieneHistorial(int $invoice_item_id): bool
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.invoiceItem', 'i_i')
            ->where('i_i.id = :invoice_item_id')
            ->setParameter('invoice_item_id', $invoice_item_id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }
}
