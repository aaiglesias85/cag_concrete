<?php

namespace App\Repository;

use App\Entity\OverheadPrice;
use Doctrine\ORM\EntityRepository;


class OverheadPriceRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los overheads
     *
     * @return OverheadPrice[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('o_p')
            ->orderBy('o_p.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarOverheads: Lista los overheads
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return OverheadPrice[]
     */
    public function ListarOverheads($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('o_p');

        if ($sSearch != "") {
            $consulta->andWhere('o_p.name LIKE :name')
                ->setParameter('name', "%${sSearch}%");
        }


        $consulta->orderBy("o_p.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalOverheads: Total de overheads de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalOverheads($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(o_p.overheadId) FROM App\Entity\OverheadPrice o_p ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (o_p.name LIKE :name) ';
            else
                $where .= 'AND (o_p.name LIKE :name) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}