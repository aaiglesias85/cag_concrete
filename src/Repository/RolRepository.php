<?php

namespace App\Repository;

use App\Entity\Rol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rol::class);
    }

    /**
     * Lista los roles ordenados
     *
     * @return Rol[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Buscar rol por nombre
     *
     * @param string $nombre
     * @return Rol|null
     */
    public function BuscarPorNombre(string $nombre): ?Rol
    {
        return $this->findOneBy(['nombre' => $nombre]);
    }

    /**
     * Lista los roles con paginación y filtrado
     *
     * @return Rol[]
     */
    public function ListarRoles(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortColumn = 'nombre',
        string  $sortDirection = 'ASC'
    ): array
    {
        $qb = $this->createQueryBuilder('r');

        if (!empty($sSearch)) {
            $qb->andWhere('r.nombre LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return $qb->orderBy('r.' . $sortColumn, $sortDirection)
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Total de roles según filtro de búsqueda
     *
     * @return int
     */
    public function TotalRoles(?string $sSearch = null): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.rolId)');

        if (!empty($sSearch)) {
            $qb->andWhere('r.nombre LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}