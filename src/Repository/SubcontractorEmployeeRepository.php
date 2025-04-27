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
        int $start,
        int $limit,
        ?string $sSearch = null,
        string $sortColumn = 'name',
        string $sortDirection = 'ASC',
        ?int $subcontractorId = null
    ): array {
        $qb = $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_e.name LIKE :search OR s_e.position LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        return $qb->orderBy('s_e.' . $sortColumn, $sortDirection)
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
            ->select('COUNT(s_e.employeeId)')
            ->leftJoin('s_e.subcontractor', 's');

        // Filtrar por búsqueda
        if ($sSearch) {
            $qb->andWhere('s_e.name LIKE :search OR s_e.position LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtrar por subcontratista
        if ($subcontractorId) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
