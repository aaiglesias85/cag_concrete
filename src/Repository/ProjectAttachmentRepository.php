<?php

namespace App\Repository;

use App\Entity\ProjectAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectAttachment::class);
    }

    /**
     * ListarAttachmentsDeProject: Lista los attachments
     *
     * @return ProjectAttachment[]
     */
    public function ListarAttachmentsDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('p_a')
            ->leftJoin('p_a.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $consulta->orderBy('p_a.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

}