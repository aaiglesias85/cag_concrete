<?php

namespace App\Repository;

use App\Entity\Unit;
use Doctrine\ORM\EntityRepository;


class UnitRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista las units
     *
     * @return Unit[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('u')
            ->where('u.status = 1')
            ->orderBy('u.description', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarUnits: Lista los units
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Unit[]
     */
    public function ListarUnits($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('u');

        if ($sSearch != "")
            $consulta->andWhere('u.description LIKE :description')
                ->setParameter('description', "%${sSearch}%");

        $consulta->orderBy("u.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalUnits: Total de units de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalUnits($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(u.unitId) FROM App\Entity\Unit u ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE u.description LIKE :description ';
            else
                $where .= 'AND u.description LIKE :description ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}