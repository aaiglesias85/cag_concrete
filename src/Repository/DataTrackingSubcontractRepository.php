<?php

namespace App\Repository;

use App\Entity\DataTrackingSubcontract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DataTrackingSubcontractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataTrackingSubcontract::class);
    }

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
     * ListarSubcontractorsDeProject: Lista los subcontractors de un project
     *
     * @return DataTrackingSubcontract[]
     */
    public function ListarSubcontractorsDeProject($project_id)
    {
        $consulta = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_s.subcontractor', 's');

        if ($project_id != '') {
            $consulta->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $consulta->groupBy('s.subcontractorId');

        $consulta->orderBy('s.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * TotalPrice: Total de quantity * price items de la BD
     * @param string $data_tracking_id
     *
     * @return float
     */
    public function TotalPrice($data_tracking_id = '', $project_item_id = '', $project_id = '', $fecha_inicial = '', $fecha_fin = '', $status = '')
    {
        $qb = $this->createQueryBuilder('d_t_s')
            ->select('SUM(d_t_s.quantity * d_t_s.price)')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_s.projectItem', 'p_i');

        if ($data_tracking_id != '') {
            $qb->andWhere('d_t.id = :data_tracking_id')
                ->setParameter('data_tracking_id', $data_tracking_id);
        }

        if ($project_item_id != '') {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        if ($project_id != '') {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($fecha_inicial != '') {
            $fecha_inicial = \DateTime::createFromFormat('m/d/Y', $fecha_inicial)->format('Y-m-d');
            $qb->andWhere('d_t.date >= :start')
                ->setParameter('start', $fecha_inicial);
        }

        if ($fecha_fin != '') {
            $fecha_fin = \DateTime::createFromFormat('m/d/Y', $fecha_fin)->format('Y-m-d');
            $qb->andWhere('d_t.date <= :end')
                ->setParameter('end', $fecha_fin);
        }

        if ($status !== '') {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getSingleScalarResult();
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
        $qb = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if (!empty($sSearch)) {
            $qb->andWhere('s.name LIKE :search OR i.description LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if ($subcontractor_id != '') {
            $qb->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        if ($fecha_inicial != '') {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $qb->andWhere('d_t.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != '') {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $qb->andWhere('d_t.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        switch ($iSortCol_0) {
            case "subcontractor":
                $qb->orderBy("s.name", $sSortDir_0);
                break;
            case "project":
                $qb->orderBy("p.name", $sSortDir_0);
                break;
            case "item":
                $qb->orderBy("i.description", $sSortDir_0);
                break;
            case "unit":
                $qb->orderBy("u.description", $sSortDir_0);
                break;
            case "date":
                $qb->orderBy("d_t.date", $sSortDir_0);
                break;
            default:
                $qb->orderBy("d_t_s.$iSortCol_0", $sSortDir_0);
                break;
        }

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)->getQuery()->getResult();
    }


    /**
     * TotalReporteSubcontractors: Total de reporte subcontractors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalReporteSubcontractors($sSearch, $subcontractor_id = '', $project_id = '', $project_item_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $qb = $this->createQueryBuilder('d_t_s')
            ->select('COUNT(d_t_s.id)')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i');

        if (!empty($sSearch)) {
            $qb->andWhere('s.name LIKE :search OR i.description LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if ($subcontractor_id != '') {
            $qb->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $qb->andWhere('p_i.id = :item_id')
                ->setParameter('item_id', $project_item_id);
        }

        if ($fecha_inicial != '') {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $qb->andWhere('d_t.date >= :inicio')
                ->setParameter('inicio', $fecha_inicial);
        }

        if ($fecha_fin != '') {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $qb->andWhere('d_t.date <= :fin')
                ->setParameter('fin', $fecha_fin);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * ListarReporteSubcontractorsConTotal: Lista y cuenta aplicando los mismos filtros.
     *
     */
    public function ListarReporteSubcontractorsConTotal(int $start, int $limit, ?string $sSearch = null, string $sortField = 'date',
                                       string $sortDir = 'DESC', ?string $subcontractor_id = '', ?string $project_id = '', ?string $project_item_id = '', ?string $fecha_inicial = '', ?string $fecha_fin = ''): array {

        // Whitelist de campos ordenables
        $sortable = [
            'id' => 'd_t_s.id',
            'date' => 'd_t.date',
            'project' => 'p.name',
            'subcontractor' => 's.name',
            'item' => 'i.description',
            'unit' => 'u.description',
            'quantity' => 'd_t_s.quantity',
            'price' => 'd_t_s.price',
            'total' => 'd_t_s.price',
        ];
        $orderBy = $sortable[$sortField] ?? 'd_t_s.date';
        $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con JOIN y filtros
        $baseQb = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if (!empty($sSearch)) {
            $baseQb->andWhere('s.name LIKE :search OR i.description LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if ($subcontractor_id != '') {
            $baseQb->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $baseQb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $baseQb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        if ($fecha_inicial != '') {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $baseQb->andWhere('d_t.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != '') {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $baseQb->andWhere('d_t.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        // ---- Datos (con paginación y orden) ----
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)
            ->setFirstResult($start);

        if ($limit > 0) {
            $dataQb->setMaxResults($limit);
        }

        $data = $dataQb->getQuery()->getResult();

        // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')
            ->select('COUNT(d_t_s.id)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,
            'total' => $total, // total con el MISMO filtro aplicado
        ];
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
        $qb = $this->createQueryBuilder('d_t_s')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i')
            ->leftJoin('i.unit', 'u')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p');

        if (!empty($sSearch)) {
            $qb->andWhere('s.name LIKE :search OR i.description LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        if ($subcontractor_id != '') {
            $qb->andWhere('s.subcontractorId = :subcontractor_id')
                ->setParameter('subcontractor_id', $subcontractor_id);
        }

        if ($project_id != '') {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        if ($project_item_id != '') {
            $qb->andWhere('p_i.id = :project_item_id')
                ->setParameter('project_item_id', $project_item_id);
        }

        if ($fecha_inicial != '') {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial)->format("Y-m-d");
            $qb->andWhere('d_t.date >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != '') {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin)->format("Y-m-d");
            $qb->andWhere('d_t.date <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * DevolverTotalReporteSubcontractors: Total de reporte subcontractors de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function DevolverTotalReporteSubcontractors(?string $sSearch = null, ?string $subcontractorId = null, ?string $projectId = null, ?string $projectItemId = null, ?string $fechaInicial = null, ?string $fechaFinal = null): float {
        $qb = $this->createQueryBuilder('d_t_s')
            ->select('SUM(d_t_s.quantity * d_t_s.price)')
            ->leftJoin('d_t_s.subcontractor', 's')
            ->leftJoin('d_t_s.dataTracking', 'd_t')
            ->leftJoin('d_t.project', 'p')
            ->leftJoin('d_t_s.projectItem', 'p_i')
            ->leftJoin('p_i.item', 'i');

        // Filtro de búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('s.name LIKE :search OR i.description LIKE :search OR p.projectNumber LIKE :search OR p.name LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $sSearch . '%');
        }

        // Filtro por Subcontractor ID
        if (!empty($subcontractorId)) {
            $qb->andWhere('s.subcontractorId = :subcontractorId')
                ->setParameter('subcontractorId', $subcontractorId);
        }

        // Filtro por Project ID
        if (!empty($projectId)) {
            $qb->andWhere('p.projectId = :projectId')
                ->setParameter('projectId', $projectId);
        }

        // Filtro por Project Item ID
        if (!empty($projectItemId)) {
            $qb->andWhere('p_i.id = :projectItemId')
                ->setParameter('projectItemId', $projectItemId);
        }

        // Filtro por fecha inicial
        if (!empty($fechaInicial)) {
            $fechaInicialDate = \DateTime::createFromFormat('m/d/Y', $fechaInicial)?->format('Y-m-d');
            $qb->andWhere('d_t.date >= :fechaInicial')
                ->setParameter('fechaInicial', $fechaInicialDate);
        }

        // Filtro por fecha final
        if (!empty($fechaFinal)) {
            $fechaFinalDate = \DateTime::createFromFormat('m/d/Y', $fechaFinal)?->format('Y-m-d');
            $qb->andWhere('d_t.date <= :fechaFinal')
                ->setParameter('fechaFinal', $fechaFinalDate);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

}