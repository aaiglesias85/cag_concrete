<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{

    /**
     * ListarSchedulesRangoFecha: Lista el schedule de un rango de fecha
     *
     * @return Schedule[]
     */
    public function ListarSchedulesRangoFecha($fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s');

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeProject: Lista el schedule de project
     *
     * @return Schedule[]
     */
    public function ListarSchedulesDeProject($project_id, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.project', 'p');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeEmployee: Lista el schedule de employee
     *
     * @return Schedule[]
     */
    public function ListarSchedulesDeEmployee($employee_id)
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.employee', 'e');

        if ($employee_id != '') {
            $consulta->andWhere('e.employeeId = :employee_id')
                ->setParameter('employee_id', $employee_id);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * BuscarEmployeeDeSchedule: Lista el lead del schedule
     *
     * @return Schedule[]
     */
    public function BuscarEmployeeDeSchedule($project_id, $day)
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.project', 'p')
            ->where('s.employee is not null');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($day != "") {
            $consulta->andWhere('s.day >= :day')
                ->setParameter('day', $day);
        }

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeContactProject: Lista el schedule de un contact project
     *
     * @return Schedule[]
     */
    public function ListarSchedulesDeContactProject($contact_id)
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.contactProject', 'p_c');

        if ($contact_id != '') {
            $consulta->andWhere('p_c.contactId = :contact_id')
                ->setParameter('contact_id', $contact_id);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedulesDeConcreteVendor: Lista el schedule de un concrete vendor
     *
     * @return Schedule[]
     */
    public function ListarSchedulesDeConcreteVendor($vendor_id)
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.concreteVendor', 'c_v');

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        $consulta->orderBy('s.day', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarSchedules: Lista los schedules
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Schedule[]
     */
    public function ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id = '', $vendor_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.project', 'p')
            ->leftJoin('s.contactProject', 'p_c')
            ->leftJoin('s.concreteVendor', 'c_v');

        if ($sSearch != "") {
            $consulta->andWhere('s.notes LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            s.description LIKE :search OR s.location LIKE :search OR p_c.name LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        switch ($iSortCol_0) {
            case "project":
                $consulta->orderBy("p.name", $sSortDir_0);
                break;
            case "contactProject":
                $consulta->orderBy("p_c.name", $sSortDir_0);
                break;
            case "concreteVendor":
                $consulta->orderBy("c_v.name", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("s.$iSortCol_0", $sSortDir_0);
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
     * TotalSchedules: Total de schedules de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalSchedules($sSearch, $project_id = '', $vendor_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->select('COUNT(s.scheduleId)')
            ->leftJoin('s.project', 'p')
            ->leftJoin('s.contactProject', 'p_c')
            ->leftJoin('s.concreteVendor', 'c_v');

        if ($sSearch != "") {
            $consulta->andWhere('s.notes LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            s.description LIKE :search OR s.location LIKE :search OR p_c.name LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarSchedulesParaCalendario: Lista los schedules para el calendario
     *
     * @return Schedule[]
     */
    public function ListarSchedulesParaCalendario($sSearch = '', $project_id = '', $vendor_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.project', 'p')
            ->leftJoin('s.contactProject', 'p_c')
            ->leftJoin('s.concreteVendor', 'c_v');

        if ($sSearch != "") {
            $consulta->andWhere('s.notes LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            s.description LIKE :search OR s.location LIKE :search OR p_c.name LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($vendor_id != '') {
            $consulta->andWhere('c_v.vendorId = :vendor_id')
                ->setParameter('vendor_id', $vendor_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('s.day <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy("s.day", 'ASC')
            ->addOrderBy("p.projectNumber", 'ASC');

        return $consulta->getQuery()->getResult();
    }

}
