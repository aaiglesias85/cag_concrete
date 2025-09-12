<?php

namespace App\Repository;

use App\Entity\SubcontractorEmployee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubcontractorEmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubcontractorEmployee::class);
    }

    /**
     * Listar los empleados de un subcontratista
     *
     * @return SubcontractorEmployee[]
     */
    public function ListarEmployeesDeSubcontractor(int $subcontractorId): array
    {
        return $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.subcontractor', 's')
            ->andWhere('s.subcontractorId = :subcontractorId')
            ->setParameter('subcontractorId', $subcontractorId)
            ->orderBy('s_e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Listar los empleados con paginación, filtrado y ordenación
     *
     * @return SubcontractorEmployee[]
     */
    public function ListarEmployees(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortColumn = 'name',
        string  $sortDirection = 'ASC',
        ?int    $subcontractorId = null
    ): array
    {
        $qb = $this->createQueryBuilder('s_e')
            ->leftJoin('s_s_e.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_s_e.name LIKE :search OR s_s_e.position LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        return $qb->orderBy('s_s_e.' . $sortColumn, $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener el total de empleados según filtros
     *
     * @return int
     */
    public function TotalEmployees(?string $sSearch = null, ?int $subcontractorId = null): int
    {
        $qb = $this->createQueryBuilder('s_e')
            ->select('COUNT(s_s_e.employeeId)')
            ->leftJoin('s_s_e.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_s_e.name LIKE :search OR s_s_e.position LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarEmployeesConTotal Lista los employees con total
     *
     * @return []
     */
    public function ListarEmployeesConTotal(int $start, int $limit, ?string $sSearch = null, string $sortColumn = 'name', string $sortDirection = 'ASC', ?int $subcontractorId = null): array
    {

        // Whitelist de columnas ordenables
        $sortable = [
            'employeeId' => 's_s_e.employeeId',
            'name' => 's_s_e.name',
            'hourlyRate' => 's_s_e.hourlyRate',
            'position' => 's_s_e.position'
        ];
        $orderBy = $sortable[$sortColumn] ?? 's_e.name';
        $dir = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('s_s_e')
            ->leftJoin('s_s_e.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $baseQb->andWhere('s_s_e.name LIKE :search OR s_s_e.position LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $baseQb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
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
            ->select('COUNT(s_s_e.employeeId)');

        $total = (int)$countQb->getQuery()->getSingleScalarResult();

        return [
            'data' => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}
