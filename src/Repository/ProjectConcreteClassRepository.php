<?php

namespace App\Repository;

use App\Entity\ProjectConcreteClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectConcreteClassRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ProjectConcreteClass::class);
   }

   /**
    * ListarConcreteClassesDeProject: Lista las concrete classes de un project
    *
    * @return ProjectConcreteClass[]
    */
   public function ListarConcreteClassesDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_c_c')
         ->leftJoin('p_c_c.project', 'p')
         ->leftJoin('p_c_c.concreteClass', 'c_c');

      if ($project_id != '') {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $consulta->orderBy('c_c.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeConcreteClass: Lista los projects de una concrete class
    *
    * @return ProjectConcreteClass[]
    */
   public function ListarProjectsDeConcreteClass($concrete_class_id)
   {
      $consulta = $this->createQueryBuilder('p_c_c')
         ->leftJoin('p_c_c.project', 'p')
         ->leftJoin('p_c_c.concreteClass', 'c_c');

      if ($concrete_class_id != '') {
         $consulta->andWhere('c_c.concreteClassId = :concrete_class_id')
            ->setParameter('concrete_class_id', $concrete_class_id);
      }

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * EliminarConcreteClassesDeProject: Elimina todas las concrete classes de un project
    */
   public function EliminarConcreteClassesDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_c_c')
         ->delete()
         ->where('p_c_c.project = :project_id')
         ->setParameter('project_id', $project_id);

      return $consulta->getQuery()->execute();
   }
}
