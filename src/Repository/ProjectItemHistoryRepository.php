<?php

namespace App\Repository;

use App\Entity\ProjectItemHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectItemHistoryRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ProjectItemHistory::class);
   }

   /**
    * ListarHistorialDeItem: Lista el historial de cambios de un ProjectItem
    *
    * @return ProjectItemHistory[]
    */
   public function ListarHistorialDeItem($project_item_id): array
   {
      $consulta = $this->createQueryBuilder('h')
         ->leftJoin('h.projectItem', 'p_i')
         ->leftJoin('h.user', 'u')
         ->where('p_i.id = :project_item_id')
         ->setParameter('project_item_id', $project_item_id)
         ->orderBy('h.createdAt', 'DESC');

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarHistorialDeUsuario: Lista el historial de cambios de un usuario
    *
    * @return ProjectItemHistory[]
    */
   public function ListarHistorialDeUsuario($user_id): array
   {
      $consulta = $this->createQueryBuilder('h')
         ->leftJoin('h.user', 'u')
         ->where('u.userId = :user_id')
         ->setParameter('user_id', $user_id);

      return $consulta->getQuery()->getResult();
   }
}

