<?php

namespace App\Repository;

use App\Entity\ConcreteVendor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConcreteVendorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConcreteVendor::class);
    }

    /**
     * Listar los subcontractors ordenados por nombre
     *
     * @return ConcreteVendor[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('c_v')
            ->orderBy('c_v.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Listar los  con filtros de búsqueda, paginación y ordenación
     *
     * @return ConcreteVendor[]
     */
    public function ListarVendors(int     $start, int     $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('c_v');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('c_v.address LIKE :search OR c_v.contactEmail LIKE :search OR
             c_v.contactName LIKE :search OR c_v.phone LIKE :search OR c_v.name LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return $qb->orderBy("c_v.$sortColumn", $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtener el total de vendors según los filtros de búsqueda
     *
     * @return int
     */
    public function TotalVendors(?string $sSearch = null): int
    {
        $qb = $this->createQueryBuilder('c_v')
            ->select('COUNT(c_v.vendorId)');

        // Filtro por búsqueda
        if ($sSearch) {
            $qb->andWhere('c_v.address LIKE :search OR c_v.contactEmail LIKE :search OR
             c_v.contactName LIKE :search OR c_v.phone LIKE :search OR c_v.name LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
