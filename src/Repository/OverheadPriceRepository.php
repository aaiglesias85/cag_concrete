<?php

namespace App\Repository;

use App\Entity\OverheadPrice;
use Doctrine\ORM\EntityRepository;

class OverheadPriceRepository extends EntityRepository
{
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
}