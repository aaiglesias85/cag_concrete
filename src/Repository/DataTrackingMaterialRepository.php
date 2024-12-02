<?php

namespace App\Repository;

use App\Entity\DataTrackingMaterial;
use Doctrine\ORM\EntityRepository;


class DataTrackingMaterialRepository extends EntityRepository
{

    /**
     * ListarMaterials: Lista los materials del data tracking
     *
     * @return DataTrackingMaterial[]
     */
    public function ListarMaterials($data_tracking_id)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->leftJoin('d_t_m.dataTracking', 'd_t');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }


        $consulta->orderBy('d_t_m.id', "ASC");


        return $consulta->getQuery()->getResult();
    }


    /**
     * ListarDataTrackingsDeMaterial: Lista el data tracking de material
     *
     * @return DataTrackingMaterial[]
     */
    public function ListarDataTrackingsDeMaterial($material_id)
    {
        $consulta = $this->createQueryBuilder('d_t_m')
            ->leftJoin('d_t_m.material', 'm');

        if ($material_id != '') {
            $consulta->andWhere('m.materialId = :material_id')
                ->setParameter('material_id', $material_id);
        }


        $consulta->orderBy('d_t_m.id', "ASC");

        return $consulta->getQuery()->getResult();
    }


    /**
     * TotalQuantity: Total de quantity materials de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalQuantity($data_tracking_id = '', $project_id = '', $material_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_m.quantity) FROM App\Entity\DataTrackingMaterial d_t_m ';
        $join = ' LEFT JOIN d_t_m.dataTracking d_t LEFT JOIN d_t.project p LEFT JOIN d_t_m.material m ';
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

        if ($material_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (m.materialId = :material_id) ';
            else
                $where .= 'AND (m.materialId = :material_id) ';
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

        $esta_query_material_id = substr_count($consulta, ':material_id');
        if ($esta_query_material_id == 1) {
            $query->setParameter('material_id', $material_id);
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
     * TotalMaterials: Total de quantity * price materials de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalMaterials($data_tracking_id = '', $material_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT SUM(d_t_m.quantity * d_t_m.price) FROM App\Entity\DataTrackingMaterial d_t_m ';
        $join = ' LEFT JOIN d_t_m.dataTracking d_t LEFT JOIN d_t_m.material m LEFT JOIN d_t.project p ';
        $where = '';

        if ($data_tracking_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (d_t.id = :data_tracking_id) ';
            else
                $where .= 'AND (d_t.id = :data_tracking_id) ';
        }

        if ($material_id != '') {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1)
                $where .= 'WHERE (m.materialId = :material_id) ';
            else
                $where .= 'AND (m.materialId = :material_id) ';
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

        $esta_query_material_id = substr_count($consulta, ':material_id');
        if ($esta_query_material_id == 1) {
            $query->setParameter('material_id', $material_id);
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