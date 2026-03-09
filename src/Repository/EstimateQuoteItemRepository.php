<?php

namespace App\Repository;

use App\Entity\EstimateQuoteItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateQuoteItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateQuoteItem::class);
    }

    /**
     * ListarItemsDeEstimate: Lista los items del estimate (a través de sus cuotas)
     *
     * @return EstimateQuoteItem[]
     */
    public function ListarItemsDeEstimate($estimate_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.quote', 'q')
            ->leftJoin('q.estimate', 'p');

        if ($estimate_id != '') {
            $consulta->andWhere('p.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarItemsDeQuote: Lista los items de una cuota
     *
     * @return EstimateQuoteItem[]
     */
    public function ListarItemsDeQuote($estimate_quote_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.quote', 'q');

        if ($estimate_quote_id != '') {
            $consulta->andWhere('q.id = :estimate_quote_id')
                ->setParameter('estimate_quote_id', $estimate_quote_id);
        }

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeItem: Lista los estimate quote items de un item
     *
     * @return EstimateQuoteItem[]
     */
    public function ListarEstimatesDeItem($item_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.item', 'i');

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * BuscarItemEstimateEnQuote: busca un item en un estimate dentro de una cuota (mismo estimate + quote + item = duplicado)
     *
     * @return EstimateQuoteItem[]
     */
    public function BuscarItemEstimateEnQuote($estimate_id, $quote_id, $item_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.quote', 'q')
            ->leftJoin('q.estimate', 'e')
            ->leftJoin('e_q.item', 'i');

        if ($estimate_id != '') {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        if ($quote_id !== '' && $quote_id !== null && is_numeric($quote_id)) {
            $consulta->andWhere('q.id = :quote_id')
                ->setParameter('quote_id', $quote_id);
        }

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimateQuoteItemsDeEquation: Lista los items de una equation
     *
     * @return EstimateQuoteItem[]
     */
    public function ListarEstimateQuoteItemsDeEquation($equation_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.equation', 'e')
            ->andWhere('e.equationId = :equation_id')
            ->setParameter('equation_id', $equation_id);

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }
}
