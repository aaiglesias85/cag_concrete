<?php

namespace App\Repository;

use App\Entity\DataTrackingLabor;
use Doctrine\ORM\EntityRepository;


class DataTrackingLaborRepository extends EntityRepository
{

    /**
     * ListarLabor: Lista la labor del data tracking
     *
     * @return DataTrackingLabor[]
     */
    public function ListarLabor($data_tracking_id)
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.dataTracking', 'd_t');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }


        $consulta->orderBy('d_t_l.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarDataTrackingsDeEmployee: Lista el data tracking de employee
     *
     * @return DataTrackingLabor[]
     */
    public function ListarDataTrackingsDeEmployee($employee_id)
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.employee', 'e');

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }


        $consulta->orderBy('d_t_l.id', "ASC");

        return $consulta->getQuery()->getResult();
    }


    /**
     * TotalHours: Total de hours employees de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalHours($data_tracking_id = '', $employee_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_l.hours) FROM App\Entity\DataTrackingLabor d_t_l ';
        $join = ' LEFT JOIN d_t_l.dataTracking d_t LEFT JOIN d_t_l.employee e ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
        }

        if ($employee_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (e.employeeId = :employee_id) ';
            else
                $where .= 'AND (e.employeeId = :employee_id) ';
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

        $esta_query_employee_id = substr_count($consulta, ':employee_id');
        if ($esta_query_employee_id == 1) {
            $query->setParameter('employee_id', $employee_id);
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
     * TotalLabor: Total de hours * rate de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalLabor($data_tracking_id = '', $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_l.hours * d_t_l.hourlyRate) FROM App\Entity\DataTrackingLabor d_t_l ';
        $join = ' LEFT JOIN d_t_l.dataTracking d_t LEFT JOIN d_t_l.employee e LEFT JOIN d_t.project p ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
        }

        if ($employee_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (e.employeeId = :employee_id) ';
            else
                $where .= 'AND (e.employeeId = :employee_id) ';
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

        $esta_query_employee_id = substr_count($consulta, ':employee_id');
        if ($esta_query_employee_id == 1) {
            $query->setParameter('employee_id', $employee_id);
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