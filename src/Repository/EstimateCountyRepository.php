<?php

namespace App\Repository;

use App\Entity\EstimateCounty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateCountyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateCounty::class);
    }

    /**
     * @return EstimateCounty[]
     */
    public function ListarCountiesDeEstimate($estimate_id): array
    {
        return $this->createQueryBuilder('ec')
            ->leftJoin('ec.county', 'c')
            ->join('ec.estimate', 'e')
            ->andWhere('e.estimateId = :estimate_id')
            ->setParameter('estimate_id', (int) $estimate_id)
            ->orderBy('c.description', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
