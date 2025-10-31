<?php

namespace App\Repository;

use App\Entity\ProjectPriceAdjustment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectPriceAdjustmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectPriceAdjustment::class);
    }

    /**
     * ListarAjustesDeProject: Lista los prices adjustments de un proyecto
     *
     * @return ProjectPriceAdjustment[]
     */
    public function ListarAjustesDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('p_p_a')
            ->leftJoin('p_p_a.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $consulta->orderBy('p_p_a.day', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarAjustesDeFecha: Lista los prices adjustments de una fecha
     *
     * @return ProjectPriceAdjustment[]
     */
    public function ListarAjustesDeFecha($day)
    {
        $consulta = $this->createQueryBuilder('p_p_a');

        if ($day != '') {
            $consulta->andWhere('p_p_a.day = :day')
                ->setParameter('day', $day);
        }

        $consulta->orderBy('p_p_a.day', "ASC");


        return $consulta->getQuery()->getResult();
    }
}