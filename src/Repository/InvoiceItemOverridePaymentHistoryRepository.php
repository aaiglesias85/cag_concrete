<?php

namespace App\Repository;

use App\Entity\InvoiceItemOverridePaymentHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InvoiceItemOverridePaymentHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceItemOverridePaymentHistory::class);
    }

    /**
     * ListarHistorialDeOverride: Lista el historial de cambios de paid_qty de un override
     *
     * @return InvoiceItemOverridePaymentHistory[]
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

    /**
     * TieneHistorial: Verifica si un override tiene historial de cambios
     */
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
     * IdsConHistorial: de la lista de override_ids, cuáles tienen al menos un registro de historial.
     *
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
            if (!$hist instanceof InvoiceItemOverridePaymentHistory) {
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
     * ListarHistorialDeProjectItem: Lista el historial de todos los overrides de un project_item
     *
     * @return InvoiceItemOverridePaymentHistory[]
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
     * ListarPorProject: historial de cambios de paid_qty de todos los overrides de ítems del proyecto.
     *
     * @return InvoiceItemOverridePaymentHistory[]
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
