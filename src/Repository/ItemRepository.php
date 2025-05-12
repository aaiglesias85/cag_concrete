<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\ORM\EntityRepository;

class ItemRepository extends EntityRepository
{
    /**
     * ListarOrdenados: Lista los items ordenados
     *
     * @return Item[]
     */
    public function ListarOrdenados(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = 1')
            ->orderBy('i.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarItemsDeUnit: Lista los items de una unidad específica
     *
     * @param string $unit_id
     * @return Item[]
     */
    public function ListarItemsDeUnit(string $unit_id): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.unit', 'u')
            ->andWhere('u.unitId = :unit_id')
            ->setParameter('unit_id', $unit_id)
            ->orderBy('i.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarItemsDeEquation: Lista los items de una ecuación específica
     *
     * @param string $equation_id
     * @return Item[]
     */
    public function ListarItemsDeEquation(string $equation_id): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.equation', 'e')
            ->andWhere('e.equationId = :equation_id')
            ->setParameter('equation_id', $equation_id)
            ->orderBy('i.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarItems: Lista los items con paginación y búsqueda
     *
     * @param int $start
     * @param int $limit
     * @param string $sSearch
     * @param string $iSortCol_0
     * @param string $sSortDir_0
     *
     * @return Item[]
     */
    public function ListarItems(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.unit', 'u');

        // Agregar filtro de búsqueda si se proporciona
        if (!empty($sSearch)) {
            $qb->andWhere('i.description LIKE :search OR u.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Ordenar según la columna seleccionada
        switch ($iSortCol_0) {
            case "unit":
                $qb->orderBy('u.description', $sSortDir_0);
                break;
            default:
                $qb->orderBy("i.$iSortCol_0", $sSortDir_0);
                break;
        }

        // Limitar los resultados con paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()
            ->getResult();
    }

    /**
     * TotalItems: Devuelve el total de items en la BD con filtro de búsqueda
     *
     * @param string $sSearch
     *
     * @return int
     */
    public function TotalItems(?string $sSearch): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.itemId)')
            ->leftJoin('i.unit', 'u');

        // Agregar filtro de búsqueda si se proporciona
        if (!empty($sSearch)) {
            $qb->andWhere('i.description LIKE :search OR u.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
