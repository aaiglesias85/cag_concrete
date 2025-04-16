<?php

namespace App\Repository;

use App\Entity\DataTracking;
use Doctrine\ORM\EntityRepository;


class DataTrackingRepository extends EntityRepository
{

    /**
     * ListarDataTracking: Lista el data tracking
     *
     * @return DataTracking[]
     */
    public function ListarDataTracking($project_id, $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->leftJoin('d_t.project', 'p');

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


        $consulta->orderBy('d_t.date', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDataTrackingsDeOverhead: Lista el data tracking de un overhead price
     *
     * @return DataTracking[]
     */
    public function ListarDataTrackingsDeOverhead($overhead_id)
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->leftJoin('d_t.overhead', 'o_p');

        if ($overhead_id != '') {
            $consulta->andWhere('o_p.overheadId = :overhead_id')
                ->setParameter('overhead_id', $overhead_id);
        }


        $consulta->orderBy('d_t.date', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDataTrackingsDeInspector: Lista el data tracking de un inspector
     *
     * @return DataTracking[]
     */
    public function ListarDataTrackingsDeInspector($inspector_id)
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->leftJoin('d_t.inspector', 'i');

        if ($inspector_id != '') {
            $consulta->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }


        $consulta->orderBy('d_t.date', "ASC");


        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarDataTrackings: Lista los datatrackings
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return DataTracking[]
     */
    public function ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id = '', $fecha_inicial = '', $fecha_fin = '', $pending = '')
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('d_t.inspector', 'ins');

        if ($sSearch != "") {
            $consulta->andWhere('p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description OR 
            d_t.crewLead LIKE :crewLead OR d_t.measuredBy LIKE :measuredBy OR 
            d_t.stationNumber LIKE :stationNumber OR d_t.notes LIKE :notes OR  d_t.otherMaterials LIKE :otherMaterials')
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%")
                ->setParameter('description', "%${sSearch}%")
                ->setParameter('crewLead', "%${sSearch}%")
                ->setParameter('stationNumber', "%${sSearch}%")
                ->setParameter('measuredBy', "%${sSearch}%")
                ->setParameter('notes', "%${sSearch}%")
                ->setParameter('otherMaterials', "%${sSearch}%");
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

        if ($pending !== '') {
            $consulta->andWhere('d_t.pending = :pending')
                ->setParameter('pending', $pending);
        }

        switch ($iSortCol_0) {
            case "project":
                $consulta->orderBy("p.name", $sSortDir_0);
                break;
            case "company":
                $consulta->orderBy("c.name", $sSortDir_0);
                break;
            case "inspector":
                $consulta->orderBy("i.name", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("d_t.$iSortCol_0", $sSortDir_0);
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
     * TotalDataTrackings: Total de data trackings de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalDataTrackings($sSearch, $project_id = '', $fecha_inicial = '', $fecha_fin = '', $pending = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(d_t.id) FROM App\Entity\DataTracking d_t ';
        $join = ' LEFT JOIN d_t.project p LEFT JOIN p.company c LEFT JOIN p.inspector ins ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description OR d_t.crewLead LIKE :crewLead OR d_t.measuredBy LIKE :measuredBy OR d_t.stationNumber LIKE :station OR d_t.notes LIKE :notes OR  d_t.otherMaterials LIKE :otherMaterials) ';
            else
                $where .= 'AND (p.projectNumber LIKE :number OR p.name LIKE :name OR p.description LIKE :description OR d_t.crewLead LIKE :crewLead OR d_t.measuredBy LIKE :measuredBy OR d_t.stationNumber LIKE :station OR d_t.notes LIKE :notes OR  d_t.otherMaterials LIKE :otherMaterials) ';
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

        if ($pending !== '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.pending = :pending) ';
            else
                $where .= 'AND (d_t.pending = :pending) ';
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

        $esta_query_crew = substr_count($consulta, ':crewLead');
        if ($esta_query_crew == 1)
            $query->setParameter(':crewLead', "%${sSearch}%");

        $esta_query_measured= substr_count($consulta, ':measuredBy');
        if ($esta_query_measured == 1)
            $query->setParameter(':measuredBy', "%${sSearch}%");

        $esta_query_station = substr_count($consulta, ':station');
        if ($esta_query_station == 1)
            $query->setParameter(':station', "%${sSearch}%");

        $esta_query_notes = substr_count($consulta, ':notes');
        if ($esta_query_notes == 1)
            $query->setParameter(':notes', "%${sSearch}%");

        $esta_query_materials = substr_count($consulta, ':otherMaterials');
        if ($esta_query_materials == 1)
            $query->setParameter(':otherMaterials', "%${sSearch}%");

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

        $esta_query_pending = substr_count($consulta, ':pending');
        if ($esta_query_pending == 1) {
            $query->setParameter('pending', $pending);
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

    /**
     * TotalOverhead: Total de $total_people * $overhead_price
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalOverhead($data_tracking_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t.totalPeople * d_t.overheadPrice) FROM App\Entity\DataTracking d_t ';
        $join = ' LEFT JOIN d_t.project p ';
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