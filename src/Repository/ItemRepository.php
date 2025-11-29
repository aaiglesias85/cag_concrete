<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
      parent::__construct($registry, Item::class);
   }
   /**
    * ListarOrdenados: Lista los items ordenados
    *
    * @return Item[]
    */
   public function ListarOrdenados(): array
   {
      return $this->createQueryBuilder('i')
         ->where('i.status = 1')
         ->orderBy('i.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarItemsDeUnit: Lista los items de una unidad específica
    *
    * @param string $unit_id
    * @return Item[]
    */
   public function ListarItemsDeUnit(string $unit_id): array
   {
      return $this->createQueryBuilder('i')
         ->leftJoin('i.unit', 'u')
         ->andWhere('u.unitId = :unit_id')
         ->setParameter('unit_id', $unit_id)
         ->orderBy('i.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarItemsDeEquation: Lista los items de una ecuación específica
    *
    * @param string $equation_id
    * @return Item[]
    */
   public function ListarItemsDeEquation(string $equation_id): array
   {
      return $this->createQueryBuilder('i')
         ->leftJoin('i.equation', 'e')
         ->andWhere('e.equationId = :equation_id')
         ->setParameter('equation_id', $equation_id)
         ->orderBy('i.name', 'ASC')
         ->getQuery()
         ->getResult();
   }

   /**
    * ListarItems: Lista los items con paginación y búsqueda
    *
    * @param int $start
    * @param int $limit
    * @param string $sSearch
    * @param string $iSortCol_0
    * @param string $sSortDir_0
    *
    * @return Item[]
    */
   public function ListarItems(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
   {
      $qb = $this->createQueryBuilder('i')
         ->leftJoin('i.unit', 'u');

      // Agregar filtro de búsqueda si se proporciona
      if (!empty($sSearch)) {
         $qb->andWhere('i.description LIKE :search OR u.description LIKE :search OR i.name LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      // Ordenar según la columna seleccionada
      switch ($iSortCol_0) {
         case "unit":
            $qb->orderBy('u.description', $sSortDir_0);
            break;
         default:
            $qb->orderBy("i.$iSortCol_0", $sSortDir_0);
            break;
      }

      // Limitar los resultados con paginación
      if ($limit > 0) {
         $qb->setMaxResults($limit);
      }

      return $qb->setFirstResult($start)
         ->getQuery()
         ->getResult();
   }

   /**
    * TotalItems: Devuelve el total de items en la BD con filtro de búsqueda
    *
    * @param string $sSearch
    *
    * @return int
    */
   public function TotalItems(?string $sSearch): int
   {
      $qb = $this->createQueryBuilder('i')
         ->select('COUNT(i.itemId)')
         ->leftJoin('i.unit', 'u');

      // Agregar filtro de búsqueda si se proporciona
      if (!empty($sSearch)) {
         $qb->andWhere('i.description LIKE :search OR u.description LIKE :search OR i.name LIKE :search')
            ->setParameter('search', "%{$sSearch}%");
      }

      return (int) $qb->getQuery()->getSingleScalarResult();
   }

   /**
    * ListarItemsConTotal Lista los items con total
    *
    * @return []
    */
   public function ListarItemsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
   {

      // Whitelist de columnas ordenables
      $sortable = [
         'itemId'  => 'i.itemId',
         'name' => 'i.name',
         'description' => 'i.description',
         'unit' => 'u.description',
         'yieldCalculation' => 'i.yieldCalculation',
         'status' => 'i.status',
      ];
      $orderBy = $sortable[$sortColumn] ?? 'i.name';
      $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

      // QB base con filtros (se reutiliza para datos y conteo)
      $baseQb = $this->createQueryBuilder('i')
         ->leftJoin('i.unit', 'u');

      if (!empty($sSearch)) {
         $baseQb->andWhere('i.description LIKE :search OR u.description LIKE :search OR i.name LIKE :search')
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
         ->select('COUNT(i.itemId)');

      $total = (int) $countQb->getQuery()->getSingleScalarResult();

      return [
         'data'  => $data,   // array<Rol>
         'total' => $total,  // total con el MISMO filtro 'search'
      ];
   }
}
