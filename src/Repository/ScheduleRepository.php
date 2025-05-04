<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{
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
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('s.dateStart >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('s.dateStop <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('s.dateStart', "ASC");

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

        $consulta->orderBy('s.dateStart', "ASC");

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

        $consulta->orderBy('s.dateStart', "ASC");

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
    public function ListarSchedules($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->leftJoin('s.project', 'p')
            ->leftJoin('s.contactProject', 'p_c')
            ->leftJoin('s.concreteVendor', 'c_v');

        if ($sSearch != "") {
            $consulta->andWhere('p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            s.description LIKE :search OR s.location LIKE :search OR p_c.name LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('s.dateStart >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('s.dateStop <= :fecha_final')
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
    public function TotalSchedules($sSearch, $project_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('s')
            ->select('COUNT(s.scheduleId)')
            ->leftJoin('s.project', 'p')
            ->leftJoin('s.contactProject', 'p_c');

        if ($sSearch != "") {
            $consulta->andWhere('p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            s.description LIKE :search OR s.location LIKE :search OR p_c.name LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('s.dateStart >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('s.dateStop <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int) $consulta->getQuery()->getSingleScalarResult();
    }
}
