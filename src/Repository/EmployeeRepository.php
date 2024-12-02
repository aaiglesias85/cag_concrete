<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\ORM\EntityRepository;


class EmployeeRepository extends EntityRepository
{

    /**
     * ListarOrdenados: Lista los employees
     *
     * @return Employee[]
     */
    public function ListarOrdenados()
    {
        $consulta = $this->createQueryBuilder('e')
            ->orderBy('e.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEmployees: Lista los employees
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Employee[]
     */
    public function ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $consulta = $this->createQueryBuilder('e');

        if ($sSearch != ""){
            $consulta->andWhere('e.name LIKE :name OR e.position LIKE :position')
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('position', "%${sSearch}%");
        }


        $consulta->orderBy("e.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalEmployees: Total de employees de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalEmployees($sSearch)
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(e.employeeId) FROM App\Entity\Employee e ';
        $join = '';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (e.name LIKE :name OR e.position LIKE :position) ';
            else
                $where .= 'AND (e.name LIKE :name OR e.position LIKE :position) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_position = substr_count($consulta, ':position');
        if ($esta_query_position == 1)
            $query->setParameter(':position', "%${sSearch}%");

        $total = $query->getSingleScalarResult();
        return $total;
    }
}