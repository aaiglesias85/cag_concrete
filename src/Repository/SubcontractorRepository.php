<?php

namespace App\Repository;

use App\Entity\Subcontractor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubcontractorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subcontractor::class);
    }

    /**
     * Listar los subcontractors ordenados por nombre
     *
     * @return Subcontractor[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Listar los subcontractors con filtros de búsqueda, paginación y ordenación
     *
     * @return Subcontractor[]
     */
    public function ListarSubcontractors(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortColumn = 'name',
        string  $sortDirection = 'ASC'
    ): array
    {
        $qb = $this->createQueryBuilder('s');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('s.companyPhone LIKE :search OR s.companyAddress LIKE :search OR s.companyName LIKE :search OR
                s.contactEmail LIKE :search OR s.contactName LIKE :search OR s.phone LIKE :search OR s.name LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return $qb->orderBy("s.$sortColumn", $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener el total de subcontractors según los filtros de búsqueda
     *
     * @return int
     */
    public function TotalSubcontractors(?string $sSearch = null): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.subcontractorId)');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('s.companyPhone LIKE :search OR s.companyAddress LIKE :search OR s.companyName LIKE :search OR
                s.contactEmail LIKE :search OR s.contactName LIKE :search OR s.phone LIKE :search OR s.name LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarSubcontractorsConTotal Lista los subcontractors con total
     *
     * @return []
     */
    public function ListarSubcontractorsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'subcontractorId'  => 's.subcontractorId',
            'name' => 's.name',
            'phone'    => 's.phone',
            'address' => 's.address',
        ];
        $orderBy = $sortable[$sortColumn] ?? 's.name';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('s');

        if (!empty($sSearch)) {
            $baseQb->andWhere('s.companyPhone LIKE :search OR s.companyAddress LIKE :search OR s.companyName LIKE :search OR
                s.contactEmail LIKE :search OR s.contactName LIKE :search OR s.phone LIKE :search OR s.name LIKE :search')
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
            ->select('COUNT(s.subcontractorId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}
