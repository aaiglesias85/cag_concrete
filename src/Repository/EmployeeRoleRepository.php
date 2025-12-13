<?php

namespace App\Repository;

use App\Entity\EmployeeRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmployeeRoleRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, EmployeeRole::class);
   }

   /**
    * Listar los employee roles ordenados por descripción
    *
    * @return EmployeeRole[]
    */
   public function ListarOrdenados(): array
   {
      return $this->createQueryBuilder('e_r')
         ->where('e_r.status = 1 OR e_r.status IS NULL')
         ->orderBy('e_r.description', 'ASC')
         ->getQuery()
         ->getResult();
   }


   /**
    * ListarEmployeeRolesConTotal Lista los concrete classes con total
    *
    * @return []
    */
   public function ListarEmployeeRolesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'roleId'  => 'e_r.roleId',
         'description' => 'e_r.description',
         'status' => 'e_r.status',
      ];
      $orderBy = $sortable[$sortColumn] ?? 'e_r.description';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('e_r');

      if (!empty($sSearch)) {
         $baseQb->andWhere('e_r.description LIKE :search')
            ->setParameter('search', '%' . $sSearch . '%');
      }

      // 1) Datos
      $dataQb = clone $baseQb;
      $dataQb->orderBy($orderBy, $dir)
         ->setFirstResult($start)
         ->setMaxResults($limit > 0 ? $limit : null);

      $data = $dataQb->getQuery()->getResult();

      // 2) Conteo aplicando MISMO filtro (sin order, solo COUNT)
      $countQb = clone $baseQb;
      $countQb->resetDQLPart('orderBy')
         ->select('COUNT(e_r.roleId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
