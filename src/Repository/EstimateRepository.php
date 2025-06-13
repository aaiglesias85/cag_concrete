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
     * ListarEstimatesDeCompany: Lista los estimates de un company
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeCompany($company_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.company', 'c');

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        $consulta->orderBy('e.name', "ASC");

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarEstimatesDeContact: Lista los estimates de un contact company
     *
     * @return Estimate[]
     */
    public function ListarEstimatesDeContact($contact_id)
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.contact', 'c');

        if ($contact_id != '') {
            $consulta->andWhere('c.contactId = :contact_id')
                ->setParameter('contact_id', $contact_id);
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
    public function ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id = '', $stage_id = '', $proposal_type_id = '',
                                    $status_id = '', $district_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('e')
            ->leftJoin('e.company', 'c')
            ->leftJoin('e.contact', 'c_c')
            ->leftJoin('e.proposalType', 'p_t')
            ->leftJoin('e.status', 'p_s')
            ->leftJoin('p.district', 'd')
            ->leftJoin('e.stage', 'p_s');

        if ($sSearch != "") {
            $consulta->andWhere('e.projectId LIKE :search OR e.name LIKE :search OR e.county LIKE :search OR e.priority LIKE :search OR
            e.bidNo LIKE :search OR e.phone LIKE :search OR e.email LIKE :search OR c.name LIKE :search OR c_c.name LIKE :search OR
            p_t.description LIKE :search OR p_s.description LIKE :search OR d.description LIKE :search OR p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($stage_id != '') {
            $consulta->andWhere('p_s.stageId = :stage_id')
                ->setParameter('stage_id', $stage_id);
        }

        if ($proposal_type_id != '') {
            $consulta->andWhere('p_t.typeId = :proposal_type_id')
                ->setParameter('proposal_type_id', $proposal_type_id);
        }

        if ($status_id != '') {
            $consulta->andWhere('p_s.statusId = :status_id')
                ->setParameter('status_id', $status_id);
        }

        if ($district_id != '') {
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('e.bidDeadlineDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('e.bidDeadlineDate <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($iSortCol_0 !== 'type' && $iSortCol_0 !== 'estimator') {
            $consulta->orderBy("e.$iSortCol_0", $sSortDir_0);
        } else {
            $consulta->orderBy("e.name", $sSortDir_0);
        }


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
    public function TotalEstimates($sSearch, $company_id = '', $stage_id = '', $proposal_type_id = '',
                                             $status_id = '', $district_id = '', $fecha_inicial = '', $fecha_fin = '')
    {
        $consulta = $this->createQueryBuilder('e')
            ->select('COUNT(e.estimateId)')
            ->leftJoin('e.company', 'c')
            ->leftJoin('e.contact', 'c_c')
            ->leftJoin('e.proposalType', 'p_t')
            ->leftJoin('e.status', 'p_s')
            ->leftJoin('p.district', 'd')
            ->leftJoin('e.stage', 'p_s');

        if ($sSearch != "") {
            $consulta->andWhere('e.projectId LIKE :search OR e.name LIKE :search OR e.county LIKE :search OR e.priority LIKE :search OR
            e.bidNo LIKE :search OR e.phone LIKE :search OR e.email LIKE :search OR c.name LIKE :search OR c_c.name LIKE :search OR
            p_t.description LIKE :search OR p_s.description LIKE :search OR d.description LIKE :search OR p_s.description LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($stage_id != '') {
            $consulta->andWhere('p_s.stageId = :stage_id')
                ->setParameter('stage_id', $stage_id);
        }

        if ($proposal_type_id != '') {
            $consulta->andWhere('p_t.typeId = :proposal_type_id')
                ->setParameter('proposal_type_id', $proposal_type_id);
        }

        if ($status_id != '') {
            $consulta->andWhere('p_s.statusId = :status_id')
                ->setParameter('status_id', $status_id);
        }

        if ($district_id != '') {
            $consulta->andWhere('d.districtId = :district_id')
                ->setParameter('district_id', $district_id);
        }

        if ($company_id != '') {
            $consulta->andWhere('c.companyId = :company_id')
                ->setParameter('company_id', $company_id);
        }

        if ($fecha_inicial != "") {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y", $fecha_inicial);
            $fecha_inicial = $fecha_inicial->format("Y-m-d");

            $consulta->andWhere('e.bidDeadlineDate >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin != "") {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y", $fecha_fin);
            $fecha_fin = $fecha_fin->format("Y-m-d");

            $consulta->andWhere('e.bidDeadlineDate <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        return (int)$consulta->getQuery()->getSingleScalarResult();
    }

}
