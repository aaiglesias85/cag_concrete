<?php

namespace App\Repository;

use App\Entity\ProjectPrevailingRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectPrevailingRoleRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ProjectPrevailingRole::class);
   }

   /**
    * ListarRolesDeProject: Lista los prevailing roles de un project
    *
    * @return ProjectPrevailingRole[]
    */
   public function ListarRolesDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_r')
         ->leftJoin('p_r.project', 'p')
         ->leftJoin('p_r.role', 'r');

      if ($project_id != '') {
         $consulta->andWhere('p.projectId = :project_id')
            ->setParameter('project_id', $project_id);
      }

      $consulta->orderBy('r.description', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarProjectsDeRole: Lista los projects que tienen un role como prevailing
    *
    * @return ProjectPrevailingRole[]
    */
   public function ListarProjectsDeRole($role_id)
   {
      $consulta = $this->createQueryBuilder('p_r')
         ->leftJoin('p_r.project', 'p')
         ->leftJoin('p_r.role', 'r');

      if ($role_id != '') {
         $consulta->andWhere('r.roleId = :role_id')
            ->setParameter('role_id', $role_id);
      }

      $consulta->orderBy('p.name', "ASC");

      return $consulta->getQuery()->getResult();
   }

   /**
    * EliminarRolesDeProject: Elimina todos los prevailing roles de un project
    */
   public function EliminarRolesDeProject($project_id)
   {
      $consulta = $this->createQueryBuilder('p_r')
         ->delete()
         ->where('p_r.project = :project_id')
         ->setParameter('project_id', $project_id);

      return $consulta->getQuery()->execute();
   }
}
