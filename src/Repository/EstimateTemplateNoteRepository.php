<?php

namespace App\Repository;

use App\Entity\Estimate;
use App\Entity\EstimateTemplateNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateTemplateNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateTemplateNote::class);
    }

    /**
     * Listar notas template asociadas a un estimate, ordenadas por descripción de la nota.
     *
     * @return EstimateTemplateNote[]
     */
    public function findByEstimateId(int $estimateId): array
    {
        $estimateRef = $this->getEntityManager()->getReference(Estimate::class, $estimateId);

        return $this->createQueryBuilder('etn')
            ->innerJoin('etn.noteItem', 'n')
            ->addSelect('n')
            ->where('etn.estimate = :estimate')
            ->setParameter('estimate', $estimateRef)
            ->orderBy('n.description', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
