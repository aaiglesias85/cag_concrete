<?php

namespace App\Repository;

use App\Entity\Estimate;
use Doctrine\ORM\EntityRepository;

class EstimateRepository extends EntityRepository
{

    /**
     * ListarEstimatesDeStage: Lista los estimates de un stage
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeStage($stage_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.stage', 'p_s');

        if ($stage_id != '') {
            $consulta->andWhere('p_s.stageId = :stage_id')
                ->setParameter('stage_id', $stage_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeProposalType: Lista los estimates de un proposal type
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeProposalType($proposal_type_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.proposalType', 'p_t');

        if ($proposal_type_id != '') {
            $consulta->andWhere('p_t.typeId = :proposal_type_id')
                ->setParameter('proposal_type_id', $proposal_type_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDePlanStatus: Lista los estimates de un plan status
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDePlanStatus($status_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.status', 'p_s');

        if ($status_id != '') {
            $consulta->andWhere('p_s.statusId = :status_id')
                ->setParameter('status_id', $status_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeDistrict: Lista los estimates de un district
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeDistrict($district_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.district', 'd');

        if ($district_id != '') {
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeCounty: Lista los estimates de un county
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeCounty($county_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.countyObj', 'c');

        if ($county_id != '') {
            $consulta->andWhere('c.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDePlanDownloading: Lista los estimates de un plan downloading
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDePlanDownloading($plan_downloading_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.planDownloading', 'p_d');

        if ($plan_downloading_id != '') {
            $consulta->andWhere('p_d.planDownloadingId = :plan_downloading_id')
                ->setParameter('plan_downloading_id', $plan_downloading_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimates: Lista los estimates
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Estimate[]
     */
    public function ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $stage_id = '', $proposal_type_id = '',
                                    $status_id = '', $county_id = '', $district_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.proposalType', 'p_t')
            ->leftJoin('e.status', 'pl_s')
            ->leftJoin('e.countyObj', 'c_o')
            ->leftJoin('e.district', 'd')
            ->leftJoin('e.stage', 'pr_s');

        if ($sSearch != "") {
            $consulta->andWhere('e.projectId LIKE :search OR e.name LIKE :search OR c_o.description LIKE :search OR e.priority LIKE :search OR
            e.bidNo LIKE :search OR e.phone LIKE :search OR e.email LIKE :search OR 
            p_t.description LIKE :search OR pl_s.description LIKE :search OR d.description LIKE :search OR pr_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($stage_id != '') {
            $consulta->andWhere('pr_s.stageId = :stage_id')
                ->setParameter('stage_id', $stage_id);
        }

        if ($proposal_type_id != '') {
            $consulta->andWhere('p_t.typeId = :proposal_type_id')
                ->setParameter('proposal_type_id', $proposal_type_id);
        }

        if ($status_id != '') {
            $consulta->andWhere('pl_s.statusId = :status_id')
                ->setParameter('status_id', $status_id);
        }

        if ($county_id != '') {
            $consulta->andWhere('c_o.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        if ($district_id != '') {
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('e.bidDeadline >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('e.bidDeadline <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        // Ordenar por columna especificada
        $sortable = [
            'estimateId'  => 'e.estimateId',
            'name' => 'e.name',
            'company' => 'e.name',
            'bidDeadline' => 'e.bidDeadline',
            'estimators' => 'e.name',
            'stage' => 'pr_s.description',
        ];
        $orderBy = $sortable[$iSortCol_0] ?? 'e.name';
        $dir     = strtoupper($sSortDir_0) === 'DESC' ? 'DESC' : 'ASC';

        $consulta->orderBy($orderBy, $dir);


        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        return $consulta->setFirstResult($start)
            ->getQuery()->getResult();
    }

    /**
     * TotalEstimates: Total de estimates de la BD
     * @param string $sSearch Para buscar
     *
     * @return int
     */
    public function TotalEstimates($sSearch, $stage_id = '', $proposal_type_id = '',
                                   $status_id = '', $county_id = '', $district_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('e')
            ->select('COUNT(e.estimateId)')
            ->leftJoin('e.proposalType', 'p_t')
            ->leftJoin('e.status', 'pl_s')
            ->leftJoin('e.countyObj', 'c_o')
            ->leftJoin('e.district', 'd')
            ->leftJoin('e.stage', 'pr_s');

        if ($sSearch != "") {
            $consulta->andWhere('e.projectId LIKE :search OR e.name LIKE :search OR c_o.description LIKE :search OR e.priority LIKE :search OR
                e.bidNo LIKE :search OR e.phone LIKE :search OR e.email LIKE :search OR p_t.description LIKE :search OR pl_s.description LIKE :search OR
                d.description LIKE :search OR pr_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($stage_id != '') {
            $consulta->andWhere('pr_s.stageId = :stage_id')
                ->setParameter('stage_id', $stage_id);
        }

        if ($proposal_type_id != '') {
            $consulta->andWhere('p_t.typeId = :proposal_type_id')
                ->setParameter('proposal_type_id', $proposal_type_id);
        }

        if ($status_id != '') {
            $consulta->andWhere('pl_s.statusId = :status_id')
                ->setParameter('status_id', $status_id);
        }

        if ($county_id != '') {
            $consulta->andWhere('c_o.countyId = :county_id')
                ->setParameter('county_id', $county_id);
        }

        if ($district_id != '') {
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('e.bidDeadline >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('e.bidDeadline <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
