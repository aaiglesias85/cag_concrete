<?php

namespace App\Repository;

use App\Entity\DataTrackingConcVendor;
use Doctrine\ORM\EntityRepository;


class DataTrackingConcVendorRepository extends EntityRepository
{

    /**
     * ListarConcVendor: Lista la conc vendor del data tracking
     *
     * @return DataTrackingConcVendor[]
     */
    public function ListarConcVendor($data_tracking_id)
    {
        $consulta = $this->createQueryBuilder('d_t_c_v')
            ->leftJoin('d_t_c_v.dataTracking', 'd_t');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }


        $consulta->orderBy('d_t_c_v.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * TotalConcUsed: Total de conc used de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalConcUsed($data_tracking_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_c_v.totalConcUsed) FROM App\Entity\DataTrackingConcVendor d_t_c_v ';
        $join = ' LEFT JOIN d_t_c_v.dataTracking d_t ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
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

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_data_tracking_id = substr_count($consulta, ':data_tracking_id');
        if ($esta_query_data_tracking_id == 1) {
            $query->setParameter('data_tracking_id', $data_tracking_id);
        }

        $esta_query_start = substr_count($consulta, ':start');
        if ($esta_query_start == 1) {
            $query->setParameter('start', $fecha_inicial);
        }

        $esta_query_end = substr_count($consulta, ':end');
        if ($esta_query_end == 1) {
            $query->setParameter('end', $fecha_fin);
        }

        return $query->getSingleScalarResult();
    }

    /**
     * TotalConcPrice: Total de hours * rate de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalConcPrice($data_tracking_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_c_v.totalConcUsed * d_t_c_v.concPrice) FROM App\Entity\DataTrackingConcVendor d_t_c_v ';
        $join = ' LEFT JOIN d_t_c_v.dataTracking d_t LEFT JOIN d_t.project p ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
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