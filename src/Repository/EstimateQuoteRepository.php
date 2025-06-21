<?php

namespace App\Repository;

use App\Entity\EstimateQuote;
use Doctrine\ORM\EntityRepository;


class EstimateQuoteRepository extends EntityRepository
{

    /**
     * ListarItemsDeEstimate: Lista los items
     *
     * @return EstimateQuote[]
     */
    public function ListarItemsDeEstimate($estimate_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.estimate', 'p');

        if ($estimate_id != '') {
            $consulta->andWhere('p.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('e_q.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarEstimatesDeItem: Lista los estimates de item
     *
     * @return EstimateQuote[]
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
     * BuscarItemEstimate: busca un item
     *
     * @return EstimateQuote[]
     */
    public function BuscarItemEstimate($estimate_id, $item_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.estimate', 'e')
            ->leftJoin('e_q.item', 'i');

        if ($estimate_id != '') {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }

        $consulta->orderBy('e_q.id', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimateQuotesDeEquation: Lista los items de una equation
     *
     * @return EstimateQuote[]
     */
    public function ListarEstimateQuotesDeEquation($equation_id)
    {
        $consulta = $this->createQueryBuilder('e_q')
            ->leftJoin('e_q.equation', 'e')
            ->andWhere('e.equationId = :equation_id')
            ->setParameter('equation_id', $equation_id);

        $consulta->orderBy('e_q.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

}