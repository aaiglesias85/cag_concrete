<?php

namespace App\Repository;

use App\Entity\Gender;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GenderRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Gender::class);
   }
   /**
    * ListarOrdenados: Lista los genders ordenados por nombre.
    *
    * @return Gender[]
    */
   public function ListarOrdenados($search = ''): array
   {
      $consulta = $this->createQueryBuilder('g');

      if ($search != '') {
         $consulta->andWhere('g.description LIKE :search OR g.code LIKE :search')
            ->setParameter('search', "%{$search}%");
      }

      $consulta->orderBy('g.description', 'ASC');

      return $consulta->getQuery()->getResult();
   }

   /**
    * ListarGendersConTotal Lista los genders con total
    *
    * @return []
    */
   public function ListarGendersConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'genderId'  => 'g.genderId',
         'description' => 'g.description',
         'code' => 'g.code',
         'classification' => 'g.classification'
      ];
      $orderBy = $sortable[$sortColumn] ?? 'g.description';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('g');

      if (!empty($sSearch)) {
         $baseQb->andWhere('g.description LIKE :search OR g.code LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
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
         ->select('COUNT(g.genderId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
