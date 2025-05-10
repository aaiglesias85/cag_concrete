<?php

namespace App\Repository;

use App\Entity\Material;
use Doctrine\ORM\EntityRepository;

class MaterialRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista los materiales ordenados
     *
     * @return Material[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.name', "ASC")
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarMaterialsDeUnit: Lista los materiales de una unidad
     *
     * @param int $unit_id
     * @return Material[]
     */
    public function ListarMaterialsDeUnit(int $unit_id): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.unit', 'u')
            ->where('u.unitId = :unit_id')
            ->setParameter('unit_id', $unit_id)
            ->orderBy('m.name', "ASC")
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarMaterials: Lista los materiales con paginación, filtros y ordenación
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección de ordenamiento
     *
     * @return Material[]
     */
    public function ListarMaterials(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.unit', 'u');

        // Agrupar el WHERE de búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('m.name LIKE :search OR u.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Ordenar resultados
        switch ($iSortCol_0) {
            case "unit":
                $qb->orderBy("u.description", $sSortDir_0);
                break;
            default:
                $qb->orderBy("m.$iSortCol_0", $sSortDir_0);
                break;
        }

        // Paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()
            ->getResult();
    }

    /**
     * TotalMaterials: Devuelve el total de materiales en la base de datos con filtros de búsqueda
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalMaterials(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.materialId)')
            ->leftJoin('m.unit', 'u');

        // Agrupar el WHERE de búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('m.name LIKE :search OR u.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
