<?php

namespace App\Repository;

use App\Entity\EstimateAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EstimateAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstimateAttachment::class);
    }

    /**
     * @return EstimateAttachment[]
     */
    public function ListarAttachmentsDeEstimate($estimate_id): array
    {
        $consulta = $this->createQueryBuilder('e_a')
            ->leftJoin('e_a.estimate', 'e');

        if ('' != $estimate_id) {
            $consulta->andWhere('e.estimateId = :estimate_id')
                ->setParameter('estimate_id', $estimate_id);
        }

        $consulta->orderBy('e_a.name', 'ASC');

        return $consulta->getQuery()->getResult();
    }
}
