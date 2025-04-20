<?php

namespace App\Repository;

use App\Entity\SubcontractorEmployee;
use Doctrine\ORM\EntityRepository;


class SubcontractorEmployeeRepository extends EntityRepository
{

    /**
     * ListarEmployeesDeSubcontractor: Lista los employees
     *
     * @return SubcontractorEmployee[]
     */
    public function ListarEmployeesDeSubcontractor($subcontractor_id)
    {
        $consulta = $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.subcontractor', 's');

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }


        $consulta->orderBy('s_e.name', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEmployees: Lista los notes
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return SubcontractorEmployee[]
     */
    public function ListarEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id = '')
    {
        $consulta = $this->createQueryBuilder('s_e')
            ->leftJoin('s_e.subcontractor', 's')
        ;

        if ($sSearch != "") {
            $consulta->andWhere('s_e.name LIKE :name OR s_e.position LIKE :position')
                ->setParameter('name', "%{$sSearch}%")
                ->setParameter('position', "%{$sSearch}%");
        }

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        $consulta->orderBy("s_e.$iSortCol_0", $sSortDir_0);

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalEmployees: Total de notes de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalEmployees($sSearch, $subcontractor_id = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(s_e.employeeId) FROM App\Entity\SubcontractorEmployee s_e ';
        $join = ' LEFT JOIN s_e.subcontractor s ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s_e.name LIKE :name OR s_e.position LIKE :position) ';
            else
                $where .= 'AND (s_e.name LIKE :name OR s_e.position LIKE :position) ';
        }

        if ($subcontractor_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.subcontractorId = :subcontractor_id) ';
            else
                $where .= 'AND (s.subcontractorId = :subcontractor_id) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%{$sSearch}%");

        $esta_query_position = substr_count($consulta, ':position');
        if ($esta_query_position == 1)
            $query->setParameter(':position', "%{$sSearch}%");

        $esta_query_subcontractor_id = substr_count($consulta, ':subcontractor_id');
        if ($esta_query_subcontractor_id == 1) {
            $query->setParameter('subcontractor_id', $subcontractor_id);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

}