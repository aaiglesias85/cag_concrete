<?php

namespace App\Repository;

use App\Entity\Inspector;
use Doctrine\ORM\EntityRepository;

class InspectorRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista los inspectores con estado activo y los ordena por nombre.
     *
     * @return Inspector[]
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
     * ListarInspectors: Lista los inspectores con filtros, paginación y ordenación.
     *
     * @param int $start El inicio de la paginación
     * @param int $limit El límite de resultados
     * @param string|null $sSearch El término de búsqueda (opcional)
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección del orden (ASC/DESC)
     *
     * @return Inspector[]
     */
    public function ListarInspectors(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('i');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Ordenar por la columna seleccionada
        $qb->orderBy("i.$iSortCol_0", $sSortDir_0);

        // Limitar los resultados con paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit)
                ->setFirstResult($start);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * TotalInspectors: Obtiene el total de inspectores en la BD con filtro de búsqueda.
     *
     * @param string|null $sSearch El término de búsqueda (opcional)
     *
     * @return int El número total de inspectores
     */
    public function TotalInspectors(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.inspectorId)');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
