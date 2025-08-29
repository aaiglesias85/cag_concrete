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

        $qb->orderBy('r.' . $sortColumn, $sortDirection);

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->setFirstResult($start)
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

    // src/Repository/RolRepository.php

    public function ListarRolesConTotal(
        int     $start,
        int     $limit,
        ?string $sSearch = null,
        string  $sortColumn = 'nombre',
        string  $sortDirection = 'ASC'
    ): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'rolId'  => 'r.rolId',
            'nombre' => 'r.nombre',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'r.nombre';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('r');

        if (!empty($sSearch)) {
            $baseQb->andWhere('r.nombre LIKE :search')
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
            ->select('COUNT(r.rolId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}