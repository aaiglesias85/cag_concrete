<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\ORM\EntityRepository;

class EmployeeRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista los employees ordenados por nombre.
     *
     * @return Employee[]
     */
    public function ListarOrdenados($position = ''): array
    {
        $consulta = $this->createQueryBuilder('e');

        if ($position != '') {
            $consulta->andWhere('e.position = :position')
                ->setParameter('position', $position);
        }

        $consulta->orderBy('e.name', 'ASC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarLeads: Lista los employees que son Lead
     *
     * @return Employee[]
     */
    public function ListarLeads(): array
    {
        return $this->createQueryBuilder('e')
            ->where("e.position = 'Lead'")
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarEmployees: Lista los employees con filtros y ordenación.
     *
     * @param int $start El inicio de la paginación
     * @param int $limit El límite de resultados
     * @param string|null $sSearch El término de búsqueda (opcional)
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección del orden (ASC/DESC)
     *
     * @return Employee[]
     */
    public function ListarEmployees(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('e');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('e.name LIKE :search OR e.position LIKE :search')
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
     * TotalEmployees: Obtiene el total de employees en la BD, con filtro de búsqueda.
     *
     * @param string|null $sSearch El término de búsqueda (opcional)
     *
     * @return int El número total de employees
     */
    public function TotalEmployees(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('e')
            ->select('COUNT(e.employeeId)');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('e.name LIKE :search OR e.position LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}