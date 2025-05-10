<?php

namespace App\Repository;

use App\Entity\Equation;
use Doctrine\ORM\EntityRepository;

class EquationRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista las ecuaciones con el estado 1 y las ordena por descripción.
     *
     * @return Equation[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = 1')
            ->orderBy('e.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarEquations: Lista las ecuaciones con filtros, paginación y ordenación.
     *
     * @param int $start El inicio de la paginación
     * @param int $limit El límite de resultados
     * @param string|null $sSearch El término de búsqueda (opcional)
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección del orden (ASC/DESC)
     *
     * @return Equation[]
     */
    public function ListarEquations(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('e');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('e.description LIKE :search OR e.equation LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Ordenar por la columna seleccionada
        $qb->orderBy("e.$iSortCol_0", $sSortDir_0);

        // Limitar los resultados con paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit)
                ->setFirstResult($start);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * TotalEquations: Obtiene el total de ecuaciones en la BD con filtro de búsqueda.
     *
     * @param string|null $sSearch El término de búsqueda (opcional)
     *
     * @return int El número total de ecuaciones
     */
    public function TotalEquations(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.equationId)');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('e.description LIKE :search OR e.equation LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
