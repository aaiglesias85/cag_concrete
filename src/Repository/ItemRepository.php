<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\ORM\EntityRepository;


class ItemRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los items
     *
     * @return Item[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('i')
            ->where('i.status = 1')
            ->orderBy('i.description', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarItemsDeUnit: Lista los items de una unidad
     *
     * @return Item[]
     */
    public function ListarItemsDeUnit($unit_id)
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.unit', 'u')
            ->andWhere('u.unitId = :unit_id')
            ->setParameter('unit_id', $unit_id)
            ->orderBy('i.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarItemsDeEquation: Lista los items de una equation
     *
     * @return Item[]
     */
    public function ListarItemsDeEquation($equation_id)
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.equation', 'e')
            ->andWhere('e.equationId = :equation_id')
            ->setParameter('equation_id', $equation_id)
            ->orderBy('i.description', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarItems: Lista los items
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Item[]
     */
    public function ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('i')
            ->leftJoin('i.unit', 'u');

        if ($sSearch != "")
            $consulta->andWhere('i.description LIKE :description OR u.description LIKE :unit')
                ->setParameter('description', "%${sSearch}%")
                ->setParameter('unit', "%${sSearch}%");

        switch ($iSortCol_0) {
            case "unit":
                $consulta->orderBy("u.description", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("i.$iSortCol_0", $sSortDir_0);
                break;
        }

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalItems: Total de items de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalItems($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(i.itemId) FROM App\Entity\Item i ';
        $join = ' LEFT JOIN i.unit u ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (i.description LIKE :description OR u.description LIKE :unit) ';
            else
                $where .= 'AND (i.description LIKE :description OR u.description LIKE :unit) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");

        $esta_query_unit = substr_count($consulta, ':unit');
        if ($esta_query_unit == 1)
            $query->setParameter(':unit', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}