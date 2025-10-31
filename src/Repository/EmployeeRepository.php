<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }
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

    /**
     * ListarEmployeesConTotal Lista los employees con total
     *
     * @return []
     */
    public function ListarEmployeesConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'employeeId'  => 'e.employeeId',
            'name' => 'e.name',
            'hourlyRate' => 'e.hourlyRate',
            'position' => 'e.position'
        ];
        $orderBy = $sortable[$sortColumn] ?? 'e.name';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('e');

        if (!empty($sSearch)) {
            $baseQb->andWhere('e.name LIKE :search OR e.position LIKE :search')
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
            ->select('COUNT(e.employeeId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}