<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverridePaymentUnpaidQtyHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverridePaymentUnpaidQtyHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemOverridePaymentUnpaidQtyHistory::class);
    }

    /**
     * @return InvoiceItemOverridePaymentUnpaidQtyHistory[]
     */
    public function ListarHistorialDeOverride(int $invoice_item_override_payment_id): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.invoiceItemOverridePayment', 'o')
            ->leftJoin('h.user', 'u')
            ->where('o.id = :override_id')
            ->setParameter('override_id', $invoice_item_override_payment_id)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function TieneHistorial(int $invoice_item_override_payment_id): bool
    {
        return $this->createQueryBuilder('h')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->where('o.id = :override_id')
            ->setParameter('override_id', $invoice_item_override_payment_id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    /**
     * @param int[] $override_ids
     * @return int[]
     */
    public function IdsConHistorial(array $override_ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $override_ids)));
        if ($ids === []) {
            return [];
        }

        $entities = $this->createQueryBuilder('h')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $seen = [];
        foreach ($entities as $hist) {
            if (!$hist instanceof InvoiceItemOverridePaymentUnpaidQtyHistory) {
                continue;
            }
            $o = $hist->getInvoiceItemOverridePayment();
            if ($o !== null) {
                $seen[$o->getId()] = true;
            }
        }

        return array_map('intval', array_keys($seen));
    }

    /**
     * @return InvoiceItemOverridePaymentUnpaidQtyHistory[]
     */
    public function ListarHistorialDeProjectItem(int $project_item_id): array
    {
        return $this->createQueryBuilder('h')
            ->leftJoin('h.invoiceItemOverridePayment', 'o')
            ->leftJoin('o.projectItem', 'p')
            ->leftJoin('h.user', 'u')
            ->where('p.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id)
            ->orderBy('h.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return InvoiceItemOverridePaymentUnpaidQtyHistory[]
     */
    public function ListarPorProject(int $projectId): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->join('o.projectItem', 'pi')
            ->join('pi.project', 'pr')
            ->leftJoin('h.user', 'u')
            ->where('pr.projectId = :pid')
            ->setParameter('pid', $projectId)
            ->orderBy('h.createdAt', 'DESC')
            ->addOrderBy('h.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByOverrideId(int $invoice_item_override_payment_id): ?InvoiceItemOverridePaymentUnpaidQtyHistory
    {
        return $this->createQueryBuilder('h')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->where('o.id = :oid')
            ->setParameter('oid', $invoice_item_override_payment_id)
            ->orderBy('h.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByOverrideId(int $invoice_item_override_payment_id): int
    {
        return (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->where('o.id = :oid')
            ->setParameter('oid', $invoice_item_override_payment_id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
