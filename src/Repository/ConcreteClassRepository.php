<?php

namespace App\Repository;

use App\Entity\ConcreteClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConcreteClassRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, ConcreteClass::class);
   }

   /**
    * Listar los subcontractors ordenados por nombre
    *
    * @return ConcreteClass[]
    */
   public function ListarOrdenados(): array
   {
      return $this->createQueryBuilder('c_c')
         ->where('c_c.status = 1 OR c_c.status IS NULL')
         ->orderBy('c_c.name', 'ASC')
         ->getQuery()
         ->getResult();
   }


   /**
    * ListarConcreteClassesConTotal Lista los concrete classes con total
    *
    * @return []
    */
   public function ListarConcreteClassesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'concreteClassId'  => 'c_c.concreteClassId',
         'name' => 'c_c.name',
      ];
      $orderBy = $sortable[$sortColumn] ?? 'c_c.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('c_c');

      if (!empty($sSearch)) {
         $baseQb->andWhere('c_c.name LIKE :search')
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
         ->select('COUNT(c_c.concreteClassId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
