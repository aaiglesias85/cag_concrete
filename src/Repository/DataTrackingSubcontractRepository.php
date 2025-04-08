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

    /**
     * ListarReporteSubcontractors: Lista el reporte subcontractors
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarReporteSubcontractors($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $subcontractor_id = '', $project_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if ($sSearch != "") {
            $consulta->andWhere('s.name LIKE :subcontractor OR i.description LIKE :item OR p.projectNumber LIKE :number OR p.name LIKE :name')
                ->setParameter('subcontractor', "%${sSearch}%")
                ->setParameter('item', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%");
        }

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $consulta->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
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
            case "subcontractor":
                $consulta->orderBy("s.name", $sSortDir_0);
                break;
            case "project":
                $consulta->orderBy("p.name", $sSortDir_0);
                break;
            case "item":
                $consulta->orderBy("i.description", $sSortDir_0);
                break;
            case "unit":
                $consulta->orderBy("u.description", $sSortDir_0);
                break;
            case "date":
                $consulta->orderBy("d_t.date", $sSortDir_0);
                break;
            default:
                $consulta->orderBy("d_t_s.$iSortCol_0", $sSortDir_0);
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
     * TotalReporteSubcontractors: Total de reporte subcontractors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalReporteSubcontractors($sSearch, $subcontractor_id = '', $project_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(d_t_s.id) FROM App\Entity\DataTrackingSubcontract d_t_s ';
        $join = ' LEFT JOIN d_t_s.subcontractor s LEFT JOIN d_t_s.dataTracking d_t LEFT JOIN d_t.project p LEFT JOIN d_t_s.projectItem p_i LEFT JOIN p_i.item i  ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.name LIKE :asdf OR i.description LIKE :qwer OR p.projectNumber LIKE :number OR p.name LIKE :name) ';
            else
                $where .= 'AND (s.name LIKE :asdf OR i.description LIKE :qwer OR p.projectNumber LIKE :number OR p.name LIKE :name) ';
        }

        if ($subcontractor_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.subcontractorId = :subcontractor_id) ';
            else
                $where .= 'AND (s.subcontractorId = :subcontractor_id) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :project_id) ';
            else
                $where .= 'AND (p.projectId = :project_id) ';
        }

        if ($project_item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.id = :item_id) ';
            else
                $where .= 'AND (p_i.id = :item_id) ';
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

        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%${sSearch}%");

        $esta_query_asdf = substr_count($consulta, ':asdfLead');
        if ($esta_query_asdf == 1)
            $query->setParameter(':asdfLead', "%${sSearch}%");

        $esta_query_qwer = substr_count($consulta, ':qwerBy');
        if ($esta_query_qwer == 1)
            $query->setParameter(':qwerBy', "%${sSearch}%");

        $esta_query_subcontractor_id = substr_count($consulta, ':subcontractor_id');
        if ($esta_query_subcontractor_id == 1) {
            $query->setParameter('subcontractor_id', $subcontractor_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_project_item_id = substr_count($consulta, ':item_id');
        if ($esta_query_project_item_id == 1) {
            $query->setParameter('item_id', $project_item_id);
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
     * ListarReporteSubcontractorsParaExcel: Lista el reporte subcontractors
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarReporteSubcontractorsParaExcel($sSearch, $subcontractor_id = '', $project_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if ($sSearch != "") {
            $consulta->andWhere('s.name LIKE :subcontractor OR i.description LIKE :item OR p.projectNumber LIKE :number OR p.name LIKE :name')
                ->setParameter('subcontractor', "%${sSearch}%")
                ->setParameter('item', "%${sSearch}%")
                ->setParameter('number', "%${sSearch}%")
                ->setParameter('name', "%${sSearch}%");
        }

        if ($subcontractor_id != '') {
            $consulta->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $consulta->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
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
     * DevolverTotalReporteSubcontractors: Total de reporte subcontractors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function DevolverTotalReporteSubcontractors($sSearch = '', $subcontractor_id = '', $project_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_s.quantity * d_t_s.price) FROM App\Entity\DataTrackingSubcontract d_t_s ';
        $join = ' LEFT JOIN d_t_s.subcontractor s LEFT JOIN d_t_s.dataTracking d_t LEFT JOIN d_t.project p LEFT JOIN d_t_s.projectItem p_i LEFT JOIN p_i.item i  ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.name LIKE :asdf OR i.description LIKE :qwer OR p.projectNumber LIKE :number OR p.name LIKE :name) ';
            else
                $where .= 'AND (s.name LIKE :asdf OR i.description LIKE :qwer OR p.projectNumber LIKE :number OR p.name LIKE :name) ';
        }

        if ($subcontractor_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (s.subcontractorId = :subcontractor_id) ';
            else
                $where .= 'AND (s.subcontractorId = :subcontractor_id) ';
        }

        if ($project_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p.projectId = :project_id) ';
            else
                $where .= 'AND (p.projectId = :project_id) ';
        }

        if ($project_item_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (p_i.id = :item_id) ';
            else
                $where .= 'AND (p_i.id = :item_id) ';
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

        $esta_query_number = substr_count($consulta, ':number');
        if ($esta_query_number == 1)
            $query->setParameter(':number', "%${sSearch}%");

        $esta_query_asdf = substr_count($consulta, ':asdfLead');
        if ($esta_query_asdf == 1)
            $query->setParameter(':asdfLead', "%${sSearch}%");

        $esta_query_qwer = substr_count($consulta, ':qwerBy');
        if ($esta_query_qwer == 1)
            $query->setParameter(':qwerBy', "%${sSearch}%");

        $esta_query_subcontractor_id = substr_count($consulta, ':subcontractor_id');
        if ($esta_query_subcontractor_id == 1) {
            $query->setParameter('subcontractor_id', $subcontractor_id);
        }

        $esta_query_project_id = substr_count($consulta, ':project_id');
        if ($esta_query_project_id == 1) {
            $query->setParameter('project_id', $project_id);
        }

        $esta_query_project_item_id = substr_count($consulta, ':item_id');
        if ($esta_query_project_item_id == 1) {
            $query->setParameter('item_id', $project_item_id);
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

}