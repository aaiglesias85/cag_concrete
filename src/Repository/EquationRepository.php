<?php

namespace App\Repository;

use App\Entity\Equation;
use Doctrine\ORM\EntityRepository;


class EquationRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista las equations
     *
     * @return Equation[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('e')
            ->where('e.status = 1')
            ->orderBy('e.description', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEquations: Lista los equations
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Equation[]
     */
    public function ListarEquations($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('e');

        if ($sSearch != "")
            $consulta->andWhere('e.description LIKE :description OR e.equation LIKE :equation')
                ->setParameter('description', "%${sSearch}%")
                ->setParameter('equation', "%${sSearch}%");

        $consulta->orderBy("e.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalEquations: Total de equations de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalEquations($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(e.equationId) FROM App\Entity\Equation e ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE e.description LIKE :description OR e.equation LIKE :equation ';
            else
                $where .= 'AND e.description LIKE :description OR e.equation LIKE :equation ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");

        $esta_query_equation = substr_count($consulta, ':equation');
        if ($esta_query_equation == 1)
            $query->setParameter(':equation', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}