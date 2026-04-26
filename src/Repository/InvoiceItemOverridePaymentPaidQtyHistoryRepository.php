<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverridePaymentPaidQtyHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverridePaymentPaidQtyHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemOverridePaymentPaidQtyHistory::class);
    }

    /**
     * @return InvoiceItemOverridePaymentPaidQtyHistory[]
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
        return null !== $this->createQueryBuilder('h')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->where('o.id = :override_id')
            ->setParameter('override_id', $invoice_item_override_payment_id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int[] $override_ids
     *
     * @return int[]
     */
    public function IdsConHistorial(array $override_ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $override_ids)));
        if ([] === $ids) {
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
            if (!$hist instanceof InvoiceItemOverridePaymentPaidQtyHistory) {
                continue;
            }
            $o = $hist->getInvoiceItemOverridePayment();
            if (null !== $o) {
                $seen[$o->getId()] = true;
            }
        }

        return array_map('intval', array_keys($seen));
    }

    public function TieneHistorialPorProjectItem(int $project_item_id): bool
    {
        $count = (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->join('h.invoiceItemOverridePayment', 'o')
            ->join('o.projectItem', 'p')
            ->where('p.id = :project_item_id')
            ->setParameter('project_item_id', $project_item_id)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * @return InvoiceItemOverridePaymentPaidQtyHistory[]
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
     * @return InvoiceItemOverridePaymentPaidQtyHistory[]
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
}
