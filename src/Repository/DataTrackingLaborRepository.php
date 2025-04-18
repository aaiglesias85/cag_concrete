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
     * ListarDataTrackingsDeEmployeeEmployee: Lista el data tracking de employee employee
     *
     * @return DataTrackingLabor[]
     */
    public function ListarDataTrackingsDeEmployeeEmployee($employee_employee_id)
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.employeeEmployee', 's_e');

        if ($employee_employee_id != '') {
            $consulta->andWhere('s_e.employeeId = :employee_employee_id')
                ->setParameter('employee_employee_id', $employee_employee_id);
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


    /**
     * ListarReporteEmployees: Lista el reporte enployees
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return DataTrackingLabor[]
     */
    public function ListarReporteEmployees($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.employee', 'e')
            ->leftJoin('d_t_l.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if ($sSearch != "") {
            $consulta->andWhere('e.name LIKE :employee OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description')
                ->setParameter('employee', "%${sSearch}%")
                ->setParameter('role', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('description', "%${sSearch}%");
        }

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('d_t.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('d_t.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        switch ($iSortCol_0) {
            case "employee":
                $consulta->orderBy("e.name", $sSortDir_0);
                break;
            case "project":
                $consulta->orderBy("p.name", $sSortDir_0);
                break;
                break;
            case "date":
                $consulta->orderBy("d_t.date", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("d_t_l.$iSortCol_0", $sSortDir_0);
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
     * TotalReporteEmployees: Total de reporte employees de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalReporteEmployees($sSearch, $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(d_t_l.id) FROM App\Entity\DataTrackingLabor d_t_l ';
        $join = ' LEFT JOIN d_t_l.employee e LEFT JOIN d_t_l.dataTracking d_t LEFT JOIN d_t.project p ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (e.name LIKE :trabajador OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description) ';
            else
                $where .= 'AND (e.name LIKE :trabajador OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description) ';
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
            if (count($esta_query) == 1) {
                $where .= 'WHERE (d_t.date >= :inicio) ';
            } else {
                $where .= ' AND (d_t.date >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (d_t.date <= :fin) ';
            } else {
                $where .= ' AND (d_t.date <= :fin) ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");

        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%${sSearch}%");

        $esta_query_trabajador = substr_count($consulta, ':trabajador');
        if ($esta_query_trabajador == 1)
            $query->setParameter(':trabajador', "%${sSearch}%");

        $esta_query_role = substr_count($consulta, ':role');
        if ($esta_query_role == 1)
            $query->setParameter(':role', "%${sSearch}%");

        $esta_query_employee_id = substr_count($consulta, ':employee_id');
        if ($esta_query_employee_id == 1) {
            $query->setParameter('employee_id', $employee_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

    /**
     * ListarReporteEmployeesParaExcel: Lista el reporte employees
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return DataTrackingLabor[]
     */
    public function ListarReporteEmployeesParaExcel($sSearch, $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.employee', 'e')
            ->leftJoin('d_t_l.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if ($sSearch != "") {
            $consulta->andWhere('e.name LIKE :employee OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description')
                ->setParameter('employee', "%${sSearch}%")
                ->setParameter('role', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('description', "%${sSearch}%");
        }

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('d_t.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('d_t.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy("d_t.date", "DESC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * DevolverTotalReporteEmployees: Total de reporte employees de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function DevolverTotalReporteEmployees($sSearch = '', $employee_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_l.hours * d_t_l.hourlyRate) FROM App\Entity\DataTrackingLabor d_t_l ';
        $join = ' LEFT JOIN d_t_l.employee e LEFT JOIN d_t_l.dataTracking d_t LEFT JOIN d_t.project p ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (e.name LIKE :trabajador OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description) ';
            else
                $where .= 'AND (e.name LIKE :trabajador OR d_t_l.role LIKE :role OR p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description) ';
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
            if (count($esta_query) == 1) {
                $where .= 'WHERE (d_t.date >= :inicio) ';
            } else {
                $where .= ' AND (d_t.date >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (d_t.date <= :fin) ';
            } else {
                $where .= ' AND (d_t.date <= :fin) ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros
        //$sSearch
        $esta_query_name = substr_count($consulta, ':name');
        if ($esta_query_name == 1)
            $query->setParameter(':name', "%${sSearch}%");

        $esta_query_description = substr_count($consulta, ':description');
        if ($esta_query_description == 1)
            $query->setParameter(':description', "%${sSearch}%");

        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%${sSearch}%");

        $esta_query_trabajador = substr_count($consulta, ':trabajador');
        if ($esta_query_trabajador == 1)
            $query->setParameter(':trabajador', "%${sSearch}%");

        $esta_query_role = substr_count($consulta, ':role');
        if ($esta_query_role == 1)
            $query->setParameter(':role', "%${sSearch}%");

        $esta_query_employee_id = substr_count($consulta, ':employee_id');
        if ($esta_query_employee_id == 1) {
            $query->setParameter('employee_id', $employee_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        return $query->getSingleScalarResult();
    }


    /**
     * ListarProjectsDeEmployee: Lista los projects de employee
     *
     * @return DataTrackingLabor[]
     */
    public function ListarProjectsDeEmployee($employee_id)
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_l.employee', 'e');

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }

        $consulta->groupBy('p.projectId');

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEmployeesDeProject: Lista los employees de un project
     *
     * @return DataTrackingLabor[]
     */
    public function ListarEmployeesDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('d_t_l')
            ->leftJoin('d_t_l.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_l.employee', 'e');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $consulta->groupBy('e.employeeId');

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

}