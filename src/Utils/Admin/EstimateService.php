<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\District;
use App\Entity\Estimate;
use App\Entity\EstimateBidDeadline;
use App\Entity\EstimateEstimator;
use App\Entity\EstimateProjectType;
use App\Entity\PlanDownloading;
use App\Entity\PlanStatus;
use App\Entity\ProjectStage;
use App\Entity\ProjectType;
use App\Entity\ProposalType;
use App\Entity\Usuario;
use App\Utils\Base;

class EstimateService extends Base
{

    /**
     * EliminarBidDeadline: Elimina un bid deadline en la BD
     * @param int $id Id
     * @author Marcel
     */
    public function EliminarBidDeadline($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(EstimateBidDeadline::class)
            ->find($id);
        /**@var EstimateBidDeadline $entity */
        if ($entity != null) {

            $estimate_name = $entity->getEstimate()->getName();
            $bid_deadline = $entity->getBidDeadline()->format('d/m/Y H:i');

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Bid Deadline Estimate";
            $log_descripcion = "The bid deadline estimate is deleted: $estimate_name Bid Deadline: $bid_deadline";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CambiarStage: Cambia stage del estiamte en la BD
     * @param int $estimate_id Id
     * @author Marcel
     */
    public function CambiarStage($estimate_id, $stage_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Estimate::class)
            ->find($estimate_id);
        /** @var Estimate $entity */
        if ($entity != null) {

            $name = $entity->getName();

            $entity->setStage(NULL);
            if ($stage_id != '') {
                $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
                    ->find($stage_id);
                $entity->setStage($project_stage);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Estimate";
            $log_descripcion = "The estimate is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;


        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosEstimate: Carga los datos de un estimate
     *
     * @param int $estimate_id Id
     *
     * @author Marcel
     */
    public function CargarDatosEstimate($estimate_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Estimate::class)
            ->find($estimate_id);
        /** @var Estimate $entity */
        if ($entity != null) {

            $arreglo_resultado['project_id'] = $entity->getProjectId();
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['bidDeadline'] = $entity->getBidDeadline() ? $entity->getBidDeadline()->format('m/d/Y H:i') : "";
            $arreglo_resultado['county'] = $entity->getCounty();
            $arreglo_resultado['priority'] = $entity->getPriority();
            $arreglo_resultado['bidNo'] = $entity->getBidNo();
            $arreglo_resultado['workHour'] = $entity->getWorkHour();
            $arreglo_resultado['phone'] = $entity->getPhone();
            $arreglo_resultado['email'] = $entity->getEmail();

            $arreglo_resultado['jobWalk'] = $entity->getJobWalk() ? $entity->getJobWalk()->format('m/d/Y H:i') : "";
            $arreglo_resultado['rfiDueDate'] = $entity->getRfiDueDate() ? $entity->getRfiDueDate()->format('m/d/Y H:i') : "";
            $arreglo_resultado['projectStart'] = $entity->getProjectStart() ? $entity->getProjectStart()->format('m/d/Y H:i') : "";
            $arreglo_resultado['projectEnd'] = $entity->getProjectEnd() ? $entity->getProjectEnd()->format('m/d/Y H:i') : "";
            $arreglo_resultado['submittedDate'] = $entity->getSubmittedDate() ? $entity->getSubmittedDate()->format('m/d/Y H:i') : "";
            $arreglo_resultado['awardedDate'] = $entity->getAwardedDate() ? $entity->getAwardedDate()->format('m/d/Y H:i') : "";
            $arreglo_resultado['lostDate'] = $entity->getLostDate() ? $entity->getLostDate()->format('m/d/Y H:i') : "";
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['sector'] = $entity->getSector();


            $arreglo_resultado['stage_id'] = $entity->getStage() != null ? $entity->getStage()->getStageId() : '';
            $arreglo_resultado['proposal_type_id'] = $entity->getProposalType() != null ? $entity->getProposalType()->getTypeId() : '';
            $arreglo_resultado['status_id'] = $entity->getStatus() != null ? $entity->getStatus()->getStatusId() : '';
            $arreglo_resultado['district_id'] = $entity->getDistrict() != null ? $entity->getDistrict()->getDistrictId() : '';
            $arreglo_resultado['plan_downloading_id'] = $entity->getPlanDownloading() != null ? $entity->getPlanDownloading()->getPlanDownloadingId() : '';

            $company_id = $entity->getCompany() != null ? $entity->getCompany()->getCompanyId() : '';
            $arreglo_resultado['company_id'] = $company_id;
            $arreglo_resultado['contact_id'] = $entity->getContact() != null ? $entity->getContact()->getContactId() : '';

            // contacts
            $contacts = $this->ListarContactsDeCompany($company_id);
            $arreglo_resultado['contacts'] = $contacts;

            // estimators ids
            $estimators_id = $this->ListarEstimatorsId($estimate_id);
            $arreglo_resultado['estimators_id'] = $estimators_id;

            // project types ids
            $project_types_id = $this->ListarProjectTypesId($estimate_id);
            $arreglo_resultado['project_types_id'] = $project_types_id;

            // bid deadlines
            $bid_deadlines = $this->ListarBidDeadlines($estimate_id);
            $arreglo_resultado['bid_deadlines'] = $bid_deadlines;

            $resultado['success'] = true;
            $resultado['estimate'] = $arreglo_resultado;
        }

        return $resultado;
    }

    // listar los bid deadlines del estimate
    private function ListarBidDeadlines($estimate_id)
    {
        $bid_deadlines = [];

        $estimate_bid_deadlines = $this->getDoctrine()->getRepository(EstimateBidDeadline::class)
            ->ListarBidDeadlineDeEstimate($estimate_id);
        foreach ($estimate_bid_deadlines as $key => $estimate_bid_deadline) {
            $bid_deadlines[] = [
                'id' => $estimate_bid_deadline->getId(),
                'bidDeadline' => $estimate_bid_deadline->getBidDeadline()->format('m/d/Y H:i'),
                'tag'=> $estimate_bid_deadline->getTag() ?? '',
                'address' => $estimate_bid_deadline->getAddress() ?? '',
                'company_id' => $estimate_bid_deadline->getCompany()->getCompanyId(),
                'company' => $estimate_bid_deadline->getCompany()->getName(),
                "posicion" => $key
            ];
        }

        return $bid_deadlines;
    }

    // listar los estimators del estimate
    private function ListarEstimatorsId($estimate_id)
    {
        $ids = [];

        $estimate_estimators = $this->getDoctrine()->getRepository(EstimateEstimator::class)
            ->ListarUsuariosDeEstimate($estimate_id);
        foreach ($estimate_estimators as $estimate_estimator) {
            $ids[] = $estimate_estimator->getUser()->getUsuarioId();
        }

        return $ids;
    }

    // listar los project types del estimate
    private function ListarProjectTypesId($estimate_id)
    {
        $ids = [];

        $estimate_project_types = $this->getDoctrine()->getRepository(EstimateProjectType::class)
            ->ListarTypesDeEstimate($estimate_id);
        foreach ($estimate_project_types as $estimate_project_type) {
            $ids[] = $estimate_project_type->getType()->getTypeId();
        }

        return $ids;
    }

    /**
     * EliminarEstimate: Elimina un rol en la BD
     * @param int $estimate_id Id
     * @author Marcel
     */
    public function EliminarEstimate($estimate_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Estimate::class)
            ->find($estimate_id);
        /**@var Estimate $entity */
        if ($entity != null) {

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($estimate_id);

            $estimate_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Estimate";
            $log_descripcion = "The estimate is deleted: $estimate_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarEstimates: Elimina los estimates seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarEstimates($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $estimate_id) {
                if ($estimate_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Estimate::class)
                        ->find($estimate_id);
                    /**@var Estimate $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($estimate_id);

                        $estimate_descripcion = $entity->getName();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Estimate";
                        $log_descripcion = "The estimate is deleted: $estimate_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The estimates could not be deleted, because they are associated with a invoice";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected estimates because they are associated with a invoice";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    // eliminar informacion relacionada
    private function EliminarInformacionRelacionada($estimate_id)
    {
        $em = $this->getDoctrine()->getManager();

        // estimators
        $estimates_estimators = $this->getDoctrine()->getRepository(EstimateEstimator::class)
            ->ListarUsuariosDeEstimate($estimate_id);
        foreach ($estimates_estimators as $estimate_estimator) {
            $em->remove($estimate_estimator);
        }

        // project types
        $estimates_project_types = $this->getDoctrine()->getRepository(EstimateProjectType::class)
            ->ListarTypesDeEstimate($estimate_id);
        foreach ($estimates_project_types as $estimate_project_type) {
            $em->remove($estimate_project_type);
        }

        // bid deadlines
        $bid_deadlines = $this->getDoctrine()->getRepository(EstimateBidDeadline::class)
            ->ListarBidDeadlineDeEstimate($estimate_id);
        foreach ($bid_deadlines as $bid_deadline) {
            $em->remove($bid_deadline);
        }

    }

    /**
     * ActualizarEstimate: Actuializa los datos del rol en la BD
     * @param int $estimate_id Id
     * @author Marcel
     */
    public function ActualizarEstimate($estimate_id, $project_id, $name, $bidDeadline, $county, $priority,
                                       $bidNo, $workHour, $phone, $email, $stage_id, $proposal_type_id, $status_id, $district_id, $company_id, $contact_id,
                                       $project_types_id, $estimators_id, $bid_deadlines, $jobWalk, $rfiDueDate, $projectStart, $projectEnd, $submittedDate,
                                                                          $awardedDate, $lostDate, $location, $sector, $plan_downloading_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Estimate::class)
            ->find($estimate_id);
        /** @var Estimate $entity */
        if ($entity != null) {

            //Verificar nombre
            $estimate = $this->getDoctrine()->getRepository(Estimate::class)
                ->findOneBy(['name' => $name]);
            if ($estimate != null && $estimate_id != $estimate->getEstimateId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The project estimate name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setProjectId($project_id);
            $entity->setName($name);
            $entity->setCounty($county);
            $entity->setPriority($priority);
            $entity->setBidNo($bidNo);
            $entity->setWorkHour($workHour);
            $entity->setPhone($phone);
            $entity->setEmail($email);
            $entity->setLocation($location);
            $entity->setSector($sector);

            $entity->setBidDeadline(NULL);
            if ($bidDeadline != '') {
                $bidDeadline = \DateTime::createFromFormat('m/d/Y H:i', $bidDeadline);
                $entity->setBidDeadline($bidDeadline);
            }

            $entity->setStage(NULL);
            if ($stage_id != '') {
                $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
                    ->find($stage_id);
                $entity->setStage($project_stage);
            }

            $entity->setProposalType(NULL);
            if ($proposal_type_id != '') {
                $proposal_type = $this->getDoctrine()->getRepository(ProposalType::class)
                    ->find($proposal_type_id);
                $entity->setProposalType($proposal_type);
            }

            $entity->setStatus(NULL);
            if ($status_id != '') {
                $plan_status = $this->getDoctrine()->getRepository(PlanStatus::class)
                    ->find($status_id);
                $entity->setStatus($plan_status);
            }

            $entity->setDistrict(NULL);
            if ($district_id != '') {
                $district = $this->getDoctrine()->getRepository(District::class)
                    ->find($district_id);
                $entity->setDistrict($district);
            }

            $entity->setCompany(NULL);
            if ($company_id != '') {
                $company = $this->getDoctrine()->getRepository(Company::class)
                    ->find($company_id);
                $entity->setCompany($company);
            }

            $entity->setContact(NULL);
            if ($contact_id != '') {
                $contact = $this->getDoctrine()->getRepository(CompanyContact::class)
                    ->find($contact_id);
                $entity->setContact($contact);
            }

            $entity->setJobWalk(NULL);
            if ($jobWalk != '') {
                $jobWalk = \DateTime::createFromFormat('m/d/Y H:i', $jobWalk);
                $entity->setJobWalk($jobWalk);
            }

            $entity->setRfiDueDate(NULL);
            if ($rfiDueDate != '') {
                $rfiDueDate = \DateTime::createFromFormat('m/d/Y H:i', $rfiDueDate);
                $entity->setRfiDueDate($rfiDueDate);
            }

            $entity->setProjectStart(NULL);
            if ($projectStart != '') {
                $projectStart = \DateTime::createFromFormat('m/d/Y H:i', $projectStart);
                $entity->setProjectStart($projectStart);
            }

            $entity->setProjectEnd(NULL);
            if ($projectEnd != '') {
                $projectEnd = \DateTime::createFromFormat('m/d/Y H:i', $projectEnd);
                $entity->setProjectEnd($projectEnd);
            }

            $entity->setSubmittedDate(NULL);
            if ($submittedDate != '') {
                $submittedDate = \DateTime::createFromFormat('m/d/Y H:i', $submittedDate);
                $entity->setSubmittedDate($submittedDate);
            }

            $entity->setAwardedDate(NULL);
            if ($awardedDate != '') {
                $awardedDate = \DateTime::createFromFormat('m/d/Y H:i', $awardedDate);
                $entity->setAwardedDate($awardedDate);
            }

            $entity->setLostDate(NULL);
            if ($lostDate != '') {
                $lostDate = \DateTime::createFromFormat('m/d/Y H:i', $lostDate);
                $entity->setLostDate($lostDate);
            }

            $entity->setPlanDownloading(NULL);
            if ($plan_downloading_id != '') {
                $plan_downloading = $this->getDoctrine()->getRepository(PlanDownloading::class)
                    ->find($plan_downloading_id);
                $entity->setPlanDownloading($plan_downloading);
            }

            // save project types
            $this->SalvarProjectTypes($entity, $project_types_id, false);

            // save estimators
            $this->SalvarEstimators($entity, $estimators_id, false);

            // bid_deadlines
            $this->SalvarBidDeadlines($entity, $bid_deadlines);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Estimate";
            $log_descripcion = "The estimate is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarBidDeadlines
     * @param $bid_deadlines
     * @param Estimate $entity
     * @return void
     */
    public function SalvarBidDeadlines($entity, $bid_deadlines)
    {
        $em = $this->getDoctrine()->getManager();

        if (!empty($bid_deadlines)) {
            foreach ($bid_deadlines as $value) {

                $bid_deadline_entity = null;

                if (is_numeric($value->id)) {
                    $bid_deadline_entity = $this->getDoctrine()->getRepository(EstimateBidDeadline::class)
                        ->find($value->id);
                }

                $is_new_bid_deadline = false;
                if ($bid_deadline_entity == null) {
                    $bid_deadline_entity = new EstimateBidDeadline();
                    $is_new_bid_deadline = true;
                }

                if ($value->bidDeadline != '') {
                    $bidDeadline = \DateTime::createFromFormat('m/d/Y H:i', $value->bidDeadline);
                    $bid_deadline_entity->setBidDeadline($bidDeadline);
                }

                $bid_deadline_entity->setTag($value->tag);
                $bid_deadline_entity->setAddress($value->address);

                if ($value->company_id != '') {
                    $company = $this->getDoctrine()->getRepository(Company::class)
                        ->find($value->company_id);
                    $bid_deadline_entity->setCompany($company);
                }

                if ($is_new_bid_deadline) {
                    $bid_deadline_entity->setEstimate($entity);

                    $em->persist($bid_deadline_entity);
                }
            }
        }

    }

    /**
     * SalvarEstimate: Guarda los datos de estimate en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarEstimate($project_id, $name, $bidDeadline, $county, $priority,
                                   $bidNo, $workHour, $phone, $email, $stage_id, $proposal_type_id, $status_id, $district_id, $company_id, $contact_id,
                                   $project_types_id, $estimators_id)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar nombre
        $estimate = $this->getDoctrine()->getRepository(Estimate::class)
            ->findOneBy(['name' => $name]);
        if ($estimate != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The project estimate name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Estimate();

        $entity->setProjectId($project_id);
        $entity->setName($name);
        $entity->setCounty($county);
        $entity->setPriority($priority);
        $entity->setBidNo($bidNo);
        $entity->setWorkHour($workHour);
        $entity->setPhone($phone);
        $entity->setEmail($email);

        if ($bidDeadline != '') {
            $bidDeadline = \DateTime::createFromFormat('m/d/Y H:i', $bidDeadline);
            $entity->setBidDeadline($bidDeadline);
        }

        if ($stage_id != '') {
            $project_stage = $this->getDoctrine()->getRepository(ProjectStage::class)
                ->find($stage_id);
            $entity->setStage($project_stage);
        }

        if ($proposal_type_id != '') {
            $proposal_type = $this->getDoctrine()->getRepository(ProposalType::class)
                ->find($proposal_type_id);
            $entity->setProposalType($proposal_type);
        }

        if ($status_id != '') {
            $plan_status = $this->getDoctrine()->getRepository(PlanStatus::class)
                ->find($status_id);
            $entity->setStatus($plan_status);
        }

        if ($district_id != '') {
            $district = $this->getDoctrine()->getRepository(District::class)
                ->find($district_id);
            $entity->setDistrict($district);
        }

        if ($company_id != '') {
            $company = $this->getDoctrine()->getRepository(Company::class)
                ->find($company_id);
            $entity->setCompany($company);
        }

        if ($contact_id != '') {
            $contact = $this->getDoctrine()->getRepository(CompanyContact::class)
                ->find($contact_id);
            $entity->setContact($contact);
        }

        $em->persist($entity);

        // save project types
        $this->SalvarProjectTypes($entity, $project_types_id);

        // save estimators
        $this->SalvarEstimators($entity, $estimators_id);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Project Estimate";
        $log_descripcion = "The project estimate is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    // salvar estimators
    public function SalvarEstimators($entity, $estimators_id, $is_new = true)
    {
        $em = $this->getDoctrine()->getManager();

        // eliminar anteriores
        if (!$is_new) {
            $estimates_estimators = $this->getDoctrine()->getRepository(EstimateEstimator::class)
                ->ListarUsuariosDeEstimate($entity->getEstimateId());
            foreach ($estimates_estimators as $estimate_estimator) {
                $em->remove($estimate_estimator);
            }
        }

        if (!empty($estimators_id)) {
            foreach ($estimators_id as $estimator_id) {
                $user_entity = $this->getDoctrine()->getRepository(Usuario::class)
                    ->find($estimator_id);
                if ($user_entity !== null) {
                    $estimate_estimator = new EstimateEstimator();

                    $estimate_estimator->setEstimate($entity);
                    $estimate_estimator->setUser($user_entity);

                    $em->persist($estimate_estimator);
                }
            }
        }
    }

    // salvar project types
    public function SalvarProjectTypes($entity, $project_types_id, $is_new = true)
    {
        $em = $this->getDoctrine()->getManager();

        // eliminar anteriores
        if (!$is_new) {
            $estimates_project_types = $this->getDoctrine()->getRepository(EstimateProjectType::class)
                ->ListarTypesDeEstimate($entity->getEstimateId());
            foreach ($estimates_project_types as $estimate_project_type) {
                $em->remove($estimate_project_type);
            }
        }

        if (!empty($project_types_id)) {
            foreach ($project_types_id as $project_type_id) {
                $project_type_entity = $this->getDoctrine()->getRepository(ProjectType::class)
                    ->find($project_type_id);
                if ($project_type_entity !== null) {
                    $estimate_project_type = new EstimateProjectType();

                    $estimate_project_type->setEstimate($entity);
                    $estimate_project_type->setType($project_type_entity);

                    $em->persist($estimate_project_type);
                }
            }
        }
    }


    /**
     * ListarEstimates: Listar los estimates
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $stage_id, $project_type_id,
                                    $proposal_type_id, $status_id, $district_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        // listar
        $lista = [];
        if ($project_type_id === "") {
            $lista = $this->getDoctrine()->getRepository(Estimate::class)
                ->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $stage_id, $proposal_type_id, $status_id, $district_id, $fecha_inicial, $fecha_fin);
        } else {
            $estimates_project_type = $this->getDoctrine()->getRepository(EstimateProjectType::class)
                ->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $company_id, $stage_id, $proposal_type_id, $status_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
            foreach ($estimates_project_type as $estimate_project_type) {
                $lista[] = $estimate_project_type->getEstimate();
            }
        }


        foreach ($lista as $value) {
            $estimate_id = $value->getEstimateId();

            $acciones = $this->ListarAcciones($estimate_id);

            $bidDeadline = $value->getBidDeadline() ? $value->getBidDeadline()->format('m/d/Y H:i') : "Not set";

            // companies
            $companies = $this->ListarCompaniesParaListado($value);

            // estimators
            $estimators = $this->ListarEstimatorsParaListado($estimate_id);

            // stage
            $stage = $this->DevolverStageParaListado($estimate_id, $value->getStage());

            $arreglo_resultado[$cont] = array(
                "id" => $estimate_id,
                "name" => $value->getName(),
                "company" => $companies,
                "bidDeadline" => $bidDeadline,
                "estimators" => $estimators,
                "stage" => $stage,
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    // listar los companies para el listado
    private function ListarCompaniesParaListado(Estimate $estimate)
    {
        $companies = [];

        if ($estimate->getCompany()) {
            $companies[] = $estimate->getCompany()->getName();
        }

        $lista = $this->getDoctrine()->getRepository(EstimateBidDeadline::class)
            ->ListarBidDeadlineDeEstimate($estimate->getEstimateId());

        foreach ($lista as $value) {
            $nombre = $value->getCompany()->getName();
            if (!in_array($nombre, $companies)) {
                $companies[] = $nombre;
            }
        }

        if (count($companies) === 0) {
            return '';
        }

        $primerNombre = htmlspecialchars($companies[0], ENT_QUOTES, 'UTF-8');
        $html = '<div class="d-inline-flex align-items-center" style="gap: 8px;">';

        $restantes = array_slice($companies, 1);

        // Estilo base para los badges
        $estiloBase = 'padding: 3px 9px; font-size: 11px;';

        // Si hay más de una empresa, agregar borde izquierdo rojo al primer badge
        $estiloPrincipal = $estiloBase;
        if (count($restantes) > 0) {
            $estiloPrincipal .= ' border-left: 3px solid red;';
        }

        // Badge principal
        $html .= '<span class="badge badge-info" style="' . $estiloPrincipal . '">' . $primerNombre . '</span>';

        if (count($restantes) > 0) {
            // Badges del popover
            $contenidoPopover = implode('', array_map(function ($c) use ($estiloBase) {
                $c = htmlspecialchars($c, ENT_QUOTES, 'UTF-8');
                return '<div class="mb-1"><span class="badge badge-info" style="' . $estiloBase . '">' . $c . '</span></div>';
            }, $restantes));

            $dataContent = htmlspecialchars($contenidoPopover, ENT_QUOTES, 'UTF-8');

            $html .= '<span class="badge badge-info popover-company" data-toggle="popover" data-html="true" data-content="' . $dataContent . '" style="' . $estiloBase . '">+' . count($restantes) . '</span>';
        }

        $html .= '</div>';

        return $html;
    }


    // devolver stage stages
    private function DevolverStageParaListado($estimate_id, ?ProjectStage $stage)
    {
        $html = "";

        if ($stage !== null) {
            $stage_id = $stage->getStageId();
            $descripcion = $stage->getDescription();
            $color = $stage->getColor();

            $html = <<<HTML
                <span class="change-stage" data-id="{$estimate_id}" data-stage="{$stage_id}" style="
                    background-color: {$color};
                    color: white;
                    padding: 4px 10px;
                    border-radius: 6px;
                    font-size: 12px;
                    font-weight: bold;
                    display: inline-block;
                    font-family: Arial, sans-serif;
                    cursor: pointer;
                ">
                    {$descripcion}
                </span>
                HTML;

        }

        return $html;
    }

    // listar los estimators para el listado
    private function ListarEstimatorsParaListado($estimate_id)
    {
        $estimators = [];

        // listar
        $lista = $this->getDoctrine()->getRepository(EstimateEstimator::class)
            ->ListarUsuariosDeEstimate($estimate_id);
        foreach ($lista as $value) {
            $nombre = $value->getUser()->getNombreCompleto();
            $siglas = $this->generarAvatarHTML($nombre);

            $estimators[] = $siglas;
        }

        return implode(" ", $estimators);
    }

    private function generarAvatarHTML($nombreCompleto)
    {
        // Extraer iniciales
        $nombreCompleto = preg_replace('/\s+/', ' ', trim($nombreCompleto));
        $partes = explode(' ', $nombreCompleto);

        if (count($partes) < 2) return '';

        $inicialNombre = strtoupper(mb_substr($partes[0], 0, 1));
        $inicialApellido = strtoupper(mb_substr($partes[1], 0, 1));
        $iniciales = $inicialNombre . $inicialApellido;

        // Generar color aleatorio en formato hexadecimal
        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));

        // HTML con estilo en línea
        $html = <<<HTML
                <div style="
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    background-color: {$color};
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    font-size: 14px;
                    font-family: Arial, sans-serif;
                    text-transform: uppercase;
                    cursor: pointer;
                " title="{$nombreCompleto}">
                    {$iniciales}
                </div>
                HTML;

        return $html;
    }

    /**
     * TotalEstimates: Total de estimates
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalEstimates($sSearch, $company_id, $stage_id, $project_type_id, $proposal_type_id, $status_id, $district_id, $fecha_inicial, $fecha_fin)
    {
        if ($project_type_id === '') {
            return $this->getDoctrine()->getRepository(Estimate::class)
                ->TotalEstimates($sSearch, $company_id, $stage_id, $proposal_type_id, $status_id, $district_id, $fecha_inicial, $fecha_fin);
        } else {
            return $this->getDoctrine()->getRepository(EstimateProjectType::class)
                ->TotalEstimates($sSearch, $company_id, $stage_id, $proposal_type_id, $status_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
        }

    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 29);

        $acciones = '';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}