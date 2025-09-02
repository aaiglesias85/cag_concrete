<?php

namespace App\Repository;

use App\Entity\Inspector;
use Doctrine\ORM\EntityRepository;

class InspectorRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista los inspectores con estado activo y los ordena por nombre.
     *
     * @return Inspector[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = 1')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarInspectors: Lista los inspectores con filtros, paginación y ordenación.
     *
     * @param int $start El inicio de la paginación
     * @param int $limit El límite de resultados
     * @param string|null $sSearch El término de búsqueda (opcional)
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección del orden (ASC/DESC)
     *
     * @return Inspector[]
     */
    public function ListarInspectors(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('i');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Ordenar por la columna seleccionada
        $qb->orderBy("i.$iSortCol_0", $sSortDir_0);

        // Limitar los resultados con paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit)
                ->setFirstResult($start);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * TotalInspectors: Obtiene el total de inspectores en la BD con filtro de búsqueda.
     *
     * @param string|null $sSearch El término de búsqueda (opcional)
     *
     * @return int El número total de inspectores
     */
    public function TotalInspectors(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.inspectorId)');

        // Agregar filtro de búsqueda si es necesario
        if (!empty($sSearch)) {
            $qb->andWhere('i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarInspectorsConTotal Lista los inspectors con total
     *
     * @return []
     */
    public function ListarInspectorsConTotal(int $start, int $limit, ?string $sSearch = null, string  $sortColumn = 'name', string  $sortDirection = 'ASC'): array {

        // Whitelist de columnas ordenables
        $sortable = [
            'inspectorId'  => 'i.inspectorId',
            'name' => 'i.name',
            'email' => 'i.email',
            'phone' => 'i.phone',
            'status' => 'i.status',
        ];
        $orderBy = $sortable[$sortColumn] ?? 'i.name';
        $dir     = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con filtros (se reutiliza para datos y conteo)
        $baseQb = $this->createQueryBuilder('i');

        if (!empty($sSearch)) {
            $baseQb->andWhere('i.name LIKE :search OR i.email LIKE :search OR i.phone LIKE :search')
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
            ->select('COUNT(i.inspectorId)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,   // array<Rol>
            'total' => $total,  // total con el MISMO filtro 'search'
        ];
    }

}
