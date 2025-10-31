<?php

namespace App\Repository;

use App\Entity\OverheadPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OverheadPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OverheadPrice::class);
    }
    /**
     * ListarOrdenados: Lista los overheads ordenados por nombre
     *
     * @return OverheadPrice[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarOverheads: Lista los overheads con filtros, paginación y ordenación
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección de ordenamiento
     *
     * @return OverheadPrice[]
     */
    public function ListarOverheads(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('o');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('o.name LIKE :name')
                ->setParameter('name', "%{$sSearch}%");
        }

        // Ordenación
        $qb->orderBy("o.$iSortCol_0", $sSortDir_0);

        // Paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()
            ->getResult();
    }

    /**
     * TotalOverheads: Devuelve el total de overheads según el filtro de búsqueda
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalOverheads(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.overheadId)');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('o.name LIKE :name')
                ->setParameter('name', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarOverheadsConTotal Lista los overheads con total
     *
     * @return []
     */
    public function ListarOverheadsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'overheadId'  => 'o.overheadId',
            'name' => 'o.name',
            'price' => 'o.price',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'o.name';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('o');

        if (!empty($sSearch)) {
            $baseQb->andWhere('o.name LIKE :name')
                ->setParameter('name', "%{$sSearch}%");
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
            ->select('COUNT(o.overheadId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }
}