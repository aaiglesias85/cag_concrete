<?php

namespace App\Repository;

use App\Entity\DataTrackingSubcontract;
use Doctrine\ORM\EntityRepository;


class DataTrackingSubcontractRepository extends EntityRepository
{

    /**
     * ListarSubcontracts: Lista los subcontracts del data tracking
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarSubcontracts($data_tracking_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.dataTracking', 'd_t');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }


        $consulta->orderBy('d_t_s.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarSubcontractsDeItem: Lista el subcontractors de item
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarSubcontractsDeItem($item_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.item', 'i');

        if ($item_id != '') {
            $consulta->andWhere('i.itemId = :item_id')
                ->setParameter('item_id', $item_id);
        }


        $consulta->orderBy('d_t_s.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSubcontractsDeItemProject: Lista el subcontractors de item project
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarSubcontractsDeItemProject($project_item_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.projectItem', 'p_i');

        if ($project_item_id != '') {
            $consulta->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }


        $consulta->orderBy('d_t_s.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSubcontractsDeSubcontractor: Lista el subcontractors de subcontractor
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarSubcontractsDeSubcontractor($subcontractor_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's');

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }


        $consulta->orderBy('d_t_s.id', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarProjectsDeSubcontractor: Lista los projects de subcontractor
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarProjectsDeSubcontractor($subcontractor_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_s.subcontractor', 's');

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        $consulta->groupBy('p.projectId');

        $consulta->orderBy('p.name', "ASC");

        return $consulta->getQuery()->getResult();
    }


    /**
     * TotalQuantity: Total de quantity items de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalQuantity($data_tracking_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_s.quantity) FROM App\Entity\DataTrackingSubcontract d_t_s ';
        $join = ' LEFT JOIN d_t_s.dataTracking d_t LEFT JOIN d_t.project p LEFT JOIN d_t_s.projectItem p_i ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
        }

        if ($project_item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.id = :project_item_id) ';
            else
                $where .= 'AND (p_i.id = :project_item_id) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.date >= :start) ';
            else
                $where .= 'AND (d_t.date >= :start) ';
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.date <= :end) ';
            else
                $where .= 'AND (d_t.date <= :end) ';
        }

        if ($status !== '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.status = :status) ';
            else
                $where .= 'AND (p.status = :status) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_data_tracking_id = substr_count($consulta, ':data_tracking_id');
        if ($esta_query_data_tracking_id == 1) {
            $query->setParameter('data_tracking_id', $data_tracking_id);
        }

        $esta_query_project_item_id = substr_count($consulta, ':project_item_id');
        if ($esta_query_project_item_id == 1) {
            $query->setParameter('project_item_id', $project_item_id);
        }

        $esta_query_start = substr_count($consulta, ':start');
        if ($esta_query_start == 1) {
            $query->setParameter('start', $fecha_inicial);
        }

        $esta_query_end = substr_count($consulta, ':end');
        if ($esta_query_end == 1) {
            $query->setParameter('end', $fecha_fin);
        }

        $esta_query_status = substr_count($consulta, ':status');
        if ($esta_query_status == 1) {
            $query->setParameter('status', $status);
        }

        return $query->getSingleScalarResult();
    }

    /**
     * TotalPrice: Total de quantity * price items de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalPrice($data_tracking_id = '', $project_item_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_s.quantity * d_t_s.price) FROM App\Entity\DataTrackingSubcontract d_t_s ';
        $join = ' LEFT JOIN d_t_s.dataTracking d_t LEFT JOIN d_t.project p JOIN d_t_s.projectItem p_i ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
        }

        if ($project_item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.id = :project_item_id) ';
            else
                $where .= 'AND (p_i.id = :project_item_id) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :project_id) ';
            else
                $where .= 'AND (p.projectId = :project_id) ';
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.date >= :start) ';
            else
                $where .= 'AND (d_t.date >= :start) ';
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.date <= :end) ';
            else
                $where .= 'AND (d_t.date <= :end) ';
        }

        if ($status !== '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.status = :status) ';
            else
                $where .= 'AND (p.status = :status) ';
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_data_tracking_id = substr_count($consulta, ':data_tracking_id');
        if ($esta_query_data_tracking_id == 1) {
            $query->setParameter('data_tracking_id', $data_tracking_id);
        }

        $esta_query_project_item_id = substr_count($consulta, ':project_item_id');
        if ($esta_query_project_item_id == 1) {
            $query->setParameter('project_item_id', $project_item_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_start = substr_count($consulta, ':start');
        if ($esta_query_start == 1) {
            $query->setParameter('start', $fecha_inicial);
        }

        $esta_query_end = substr_count($consulta, ':end');
        if ($esta_query_end == 1) {
            $query->setParameter('end', $fecha_fin);
        }

        $esta_query_status = substr_count($consulta, ':status');
        if ($esta_query_status == 1) {
            $query->setParameter('status', $status);
        }

        return $query->getSingleScalarResult();
    }

}