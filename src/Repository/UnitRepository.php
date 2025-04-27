<?php

namespace App\Repository;

use App\Entity\Unit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UnitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    /**
     * Listar las unidades ordenadas por descripción
     *
     * @return Unit[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.status = 1')
            ->orderBy('u.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Listar unidades con filtros de búsqueda, paginación y ordenación
     *
     * @return Unit[]
     */
    public function ListarUnits(int $start, int $limit, ?string $sSearch = null, string $sortColumn = 'description', string $sortDirection = 'ASC'): array {
        $qb = $this->createQueryBuilder('u');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('u.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return $qb->orderBy("u.$sortColumn", $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener el total de unidades según los filtros de búsqueda
     *
     * @return int
     */
    public function TotalUnits(?string $sSearch = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.unitId)');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('u.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}