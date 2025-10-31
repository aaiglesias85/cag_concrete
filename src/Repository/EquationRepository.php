<?php

namespace App\Repository;

use App\Entity\Equation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equation::class);
    }
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

    /**
     * ListarEquationsConTotal Lista los equations con total
     *
     * @return []
     */
    public function ListarEquationsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'description', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'equationId'  => 'e.equationId',
            'description' => 'e.description',
            'equation' => 'e.equation',
            'status' => 'e.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'e.description';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('e');

        if (!empty($sSearch)) {
            $baseQb->andWhere('e.description LIKE :search OR e.equation LIKE :search')
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
            ->select('COUNT(e.equationId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}
