<?php

namespace App\Repository;

use App\Entity\ProjectCounty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectCountyRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ProjectCounty::class);
   }

   /**
    * ListarCountysDeProject: Lista los counties de un project
    *
    * @return ProjectCounty[]
    */
   public function ListarCountysDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_c')
         ->leftJoin('p_c.project', 'p')
         ->leftJoin('p_c.county', 'c');

      if ($project_id != '') {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $consulta->orderBy('c.description', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeCounty: Lista los projects de un county
    *
    * @return ProjectCounty[]
    */
   public function ListarProjectsDeCounty($county_id)
   {
      $consulta = $this->createQueryBuilder('p_c')
         ->leftJoin('p_c.project', 'p')
         ->leftJoin('p_c.county', 'c');

      if ($county_id != '') {
         $consulta->andWhere('c.countyId = :county_id')
            ->setParameter('county_id', $county_id);
      }

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * EliminarCountysDeProject: Elimina todos los counties de un project
    */
   public function EliminarCountysDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_c')
         ->delete()
         ->where('p_c.project = :project_id')
         ->setParameter('project_id', $project_id);

      return $consulta->getQuery()->execute();
   }
}
