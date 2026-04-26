<?php

namespace App\Repository;

use App\Entity\EstimateQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateQuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateQuote::class);
    }

    /**
     * ListarQuotesDeEstimate: Lista las cuotas de un estimate.
     *
     * @return EstimateQuote[]
     */
    public function ListarQuotesDeEstimate($estimate_id)
    {
        $consulta = $this->createQueryBuilder('q')
            ->leftJoin('q.estimate', 'e');

        if ('' != $estimate_id) {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('q.name', 'ASC');

        return $consulta->getQuery()->getResult();
    }
}
