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
     * ListarProjectsDeInspector: Lista los projects de inspector
     *
     * @return DataTracking[]
     */
    public function ListarProjectsDeInspector($inspector_id)
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t.inspector', 'i');

        if ($inspector_id != '') {
            $consulta->andWhere('i.inspectorId = :inspector_id')
                ->setParameter('inspector_id', $inspector_id);
        }

        $consulta->groupBy('p.projectId');

        $consulta->orderBy('p.name', "ASC");

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
            $consulta->andWhere('p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            d_t.crewLead LIKE :search OR d_t.measuredBy LIKE :search OR d_t.stationNumber LIKE :search OR d_t.notes LIKE :search OR
              d_t.otherMaterials LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
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
                $consulta->orderBy("ins.name", $sSortDir_0);
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
     * @return int
     */
    public function TotalDataTrackings($sSearch, $project_id = '', $fecha_inicial = '', $fecha_fin = '', $pending = '')
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->select('COUNT(d_t.id)')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('p.company', 'c')
            ->leftJoin('d_t.inspector', 'ins');

        if ($sSearch != "") {
            $consulta->andWhere('p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search OR 
            d_t.crewLead LIKE :search OR d_t.measuredBy LIKE :search OR d_t.stationNumber LIKE :search OR d_t.notes LIKE :search OR
              d_t.otherMaterials LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
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

            $consulta->andWhere('d_t.date <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        if ($pending !== '') {
            $consulta->andWhere('d_t.pending = :pending')
                ->setParameter('pending', $pending);
        }

        return (int) $consulta->getQuery()->getSingleScalarResult();
    }

    /**
     * TotalOverhead: Total de $total_people * $overhead_price
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalOverhead($data_tracking_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $consulta = $this->createQueryBuilder('d_t')
            ->select('SUM(d_t.totalPeople * d_t.overheadPrice)')
            ->leftJoin('d_t.project', 'p');

        if ($data_tracking_id != '') {
            $consulta->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('d_t.date >= :start')
                ->setParameter('start', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('d_t.date <= :end')
                ->setParameter('end', $fecha_fin);
        }

        if ($status !== '') {
            $consulta->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return (float) $consulta->getQuery()->getSingleScalarResult();
    }
}
