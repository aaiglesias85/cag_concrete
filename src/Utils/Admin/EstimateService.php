<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\CompanyContact;
use App\Entity\County;
use App\Entity\District;
use App\Entity\Equation;
use App\Entity\Estimate;
use App\Entity\EstimateBidDeadline;
use App\Entity\EstimateCompany;
use App\Entity\EstimateEstimator;
use App\Entity\EstimateProjectType;
use App\Entity\EstimateQuote;
use App\Entity\Item;
use App\Entity\PlanDownloading;
use App\Entity\PlanStatus;
use App\Entity\ProjectStage;
use App\Entity\ProjectType;
use App\Entity\ProposalType;
use App\Entity\Usuario;
use App\Repository\EstimateBidDeadlineRepository;
use App\Repository\EstimateCompanyRepository;
use App\Repository\EstimateEstimatorRepository;
use App\Repository\EstimateProjectTypeRepository;
use App\Repository\EstimateQuoteRepository;
use App\Repository\EstimateRepository;
use App\Utils\Base;

class EstimateService extends Base
{

   /**
    * EliminarCompany: Elimina un company en la BD
    * @param int $id Id
    * @author Marcel
    */
   public function EliminarCompany($id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EstimateCompany::class)
         ->find($id);
      /**@var EstimateCompany $entity */
      if ($entity != null) {

         $estimate_name = $entity->getEstimate()->getName();
         $company_name = $entity->getCompany()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Company Estimate";
         $log_descripcion = "The company estimate is deleted: $estimate_name Company: $company_name";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

   /**
    * AgregarItem
    * @param $item_id
    * @param $item_name
    * @param $unit_id
    * @param $quantity
    * @param $price
    * @param $yield_calculation
    * @param $equation_id
    * @return array
    */
   public function AgregarItem($estimate_item_id, $estimate_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id)
   {
      $resultado = [];

      $em = $this->getDoctrine()->getManager();

      // validar si existe
      if ($item_id !== '') {
         /** @var EstimateQuoteRepository $estimateQuoteRepo */
         $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
         $estimate_item = $estimateQuoteRepo->BuscarItemEstimate($estimate_id, $item_id);
         if (!empty($estimate_item) && $estimate_item_id != $estimate_item[0]->getId()) {
            $resultado['success'] = false;
            $resultado['error'] = "The item already exists in the project estimate";
            return $resultado;
         }
      } else {

         //Verificar description
         $item = $this->getDoctrine()->getRepository(Item::class)
            ->findOneBy(['description' => $item_name]);
         if ($item_id == '' && $item != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The item name is in use, please try entering another one.";
            return $resultado;
         }
      }


      $estimate_entity = $this->getDoctrine()->getRepository(Estimate::class)->find($estimate_id);
      if ($estimate_entity != null) {
         $estimate_item_entity = null;

         if (is_numeric($estimate_item_id)) {
            $estimate_item_entity = $this->getDoctrine()->getRepository(EstimateQuote::class)
               ->find($estimate_item_id);
         }

         $is_new_estimate_item = false;
         if ($estimate_item_entity == null) {
            $estimate_item_entity = new EstimateQuote();
            $is_new_estimate_item = true;
         }

         $estimate_item_entity->setYieldCalculation($yield_calculation);

         $price = $price !== "" ? $price : NULL;
         $estimate_item_entity->setPrice($price);

         $estimate_item_entity->setQuantity($quantity);

         $equation_entity = null;
         if ($equation_id != '') {
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
            $estimate_item_entity->setEquation($equation_entity);
         }

         $is_new_item = false;
         if ($item_id != '') {
            $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($item_id);
         } else {
            // add new item
            $new_item_data = json_encode([
               'item' => $item_name,
               'price' => $price,
               'yield_calculation' => $yield_calculation,
               'unit_id' => $unit_id
            ]);
            $item_entity = $this->AgregarNewItem(json_decode($new_item_data), $equation_entity);

            $is_new_item = true;
         }

         $estimate_item_entity->setItem($item_entity);

         if ($is_new_estimate_item) {
            $estimate_item_entity->setEstimate($estimate_entity);

            $em->persist($estimate_item_entity);
         }

         $em->flush();

         $resultado['success'] = true;

         // devolver item
         $item = $this->DevolverItemDeEstimate($estimate_item_entity);
         $resultado['item'] = $item;
         $resultado['is_new_item'] = $is_new_item;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = 'The project not exist';
      }

      return $resultado;
   }

   /**
    * EliminarItem: Elimina un item en la BD
    * @param int $estimate_item_id Id
    * @author Marcel
    */
   public function EliminarItem($estimate_item_id)
   {
      $em = $this->getDoctrine()->getManager();

      $entity = $this->getDoctrine()->getRepository(EstimateQuote::class)
         ->find($estimate_item_id);
      /**@var EstimateQuote $entity */
      if ($entity != null) {

         $item_name = $entity->getItem()->getName();

         $em->remove($entity);
         $em->flush();

         //Salvar log
         $log_operacion = "Delete";
         $log_categoria = "Estimate Item";
         $log_descripcion = "The item: $item_name of the project estimate is deleted";
         $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

         $resultado['success'] = true;
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "The requested record does not exist";
      }

      return $resultado;
   }

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
         $arreglo_resultado['bidDescription'] = $entity->getBidDescription();
         $arreglo_resultado['bidInstructions'] = $entity->getBidInstructions();
         $arreglo_resultado['planLink'] = $entity->getPlanLink();
         $arreglo_resultado['quoteReceived'] = $entity->getQuoteReceived();

         $arreglo_resultado['stage_id'] = $entity->getStage() != null ? $entity->getStage()->getStageId() : '';
         $arreglo_resultado['proposal_type_id'] = $entity->getProposalType() != null ? $entity->getProposalType()->getTypeId() : '';
         $arreglo_resultado['status_id'] = $entity->getStatus() != null ? $entity->getStatus()->getStatusId() : '';

         $county_id = $entity->getCountyObj() ? $entity->getCountyObj()->getCountyId() : null;
         $arreglo_resultado['county_id'] = $county_id;

         $arreglo_resultado['district_id'] = $entity->getDistrict() != null ? $entity->getDistrict()->getDistrictId() : '';
         $arreglo_resultado['plan_downloading_id'] = $entity->getPlanDownloading() != null ? $entity->getPlanDownloading()->getPlanDownloadingId() : '';

         // estimators ids
         $estimators_id = $this->ListarEstimatorsId($estimate_id);
         $arreglo_resultado['estimators_id'] = $estimators_id;

         // project types ids
         $project_types_id = $this->ListarProjectTypesId($estimate_id);
         $arreglo_resultado['project_types_id'] = $project_types_id;

         // bid deadlines
         $bid_deadlines = $this->ListarBidDeadlines($estimate_id);
         $arreglo_resultado['bid_deadlines'] = $bid_deadlines;

         // items
         $items = $this->ListarItemsDeEstimate($estimate_id);
         $arreglo_resultado['items'] = $items;

         // companys
         $companys = $this->ListarCompanys($estimate_id);
         $arreglo_resultado['companys'] = $companys;

         $resultado['success'] = true;
         $resultado['estimate'] = $arreglo_resultado;
      }

      return $resultado;
   }

   // listar los companys del estimate
   private function ListarCompanys($estimate_id)
   {
      $companys = [];

      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $estimate_companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate_id);
      foreach ($estimate_companys as $key => $estimate_company) {


         $contact_id = "";
         $contact = "";
         $email = "";
         $phone = "";
         if ($estimate_company->getContact()) {
            $contact_id = $estimate_company->getContact()->getContactId();
            $contact = $estimate_company->getContact()->getName();
            $email = $estimate_company->getContact()->getEmail();
            $phone = $estimate_company->getContact()->getPhone();
         }

         // contacts
         $contacts = $this->ListarContactsDeCompany($estimate_company->getCompany()->getCompanyId());


         $companys[] = [
            'id' => $estimate_company->getId(),
            'company_id' => $estimate_company->getCompany()->getCompanyId(),
            'company' => $estimate_company->getCompany()->getName(),
            'contact_id' => $contact_id,
            'contact' => $contact,
            'email' => $email,
            'phone' => $phone,
            'contacts' => $contacts,
            "posicion" => $key
         ];
      }

      return $companys;
   }

   /**
    * ListarItemsDeEstimate
    * @param $estimate_id
    * @return array
    */
   public function ListarItemsDeEstimate($estimate_id)
   {
      $items = [];

      /** @var EstimateQuoteRepository $estimateQuoteRepo */
      $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
      $lista = $estimateQuoteRepo->ListarItemsDeEstimate($estimate_id);
      foreach ($lista as $key => $value) {

         $item = $this->DevolverItemDeEstimate($value, $key);
         $items[] = $item;
      }

      return $items;
   }

   /**
    * DevolverItemDeEstimate
    * @param EstimateQuote $value
    * @return array
    */
   public function DevolverItemDeEstimate($value, $key = -1)
   {
      $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

      $quantity = $value->getQuantity();
      $price = $value->getPrice();
      $total = $quantity * $price;

      return [
         'estimate_item_id' => $value->getId(),
         "item_id" => $value->getItem()->getItemId(),
         "item" => $value->getItem()->getName(),
         "unit" => $value->getItem()->getUnit() != null ? $value->getItem()->getUnit()->getDescription() : '',
         "quantity" => $quantity,
         "price" => $price,
         "total" => $total,
         "yield_calculation" => $value->getYieldCalculation(),
         "yield_calculation_name" => $yield_calculation_name,
         "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
         "posicion" => $key
      ];
   }

   // listar los bid deadlines del estimate
   private function ListarBidDeadlines($estimate_id)
   {
      $bid_deadlines = [];

      /** @var EstimateBidDeadlineRepository $estimateBidDeadlineRepo */
      $estimateBidDeadlineRepo = $this->getDoctrine()->getRepository(EstimateBidDeadline::class);
      $estimate_bid_deadlines = $estimateBidDeadlineRepo->ListarBidDeadlineDeEstimate($estimate_id);
      foreach ($estimate_bid_deadlines as $key => $estimate_bid_deadline) {
         $bid_deadlines[] = [
            'id' => $estimate_bid_deadline->getId(),
            'bidDeadline' => $estimate_bid_deadline->getBidDeadline()->format('m/d/Y H:i'),
            'tag' => $estimate_bid_deadline->getTag() ?? '',
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

      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $estimate_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
      foreach ($estimate_estimators as $estimate_estimator) {
         $ids[] = $estimate_estimator->getUser()->getUsuarioId();
      }

      return $ids;
   }

   // listar los project types del estimate
   private function ListarProjectTypesId($estimate_id)
   {
      $ids = [];

      /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
      $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
      $estimate_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($estimate_id);
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
      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $estimates_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
      foreach ($estimates_estimators as $estimate_estimator) {
         $em->remove($estimate_estimator);
      }

      // project types
      /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
      $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
      $estimates_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($estimate_id);
      foreach ($estimates_project_types as $estimate_project_type) {
         $em->remove($estimate_project_type);
      }

      // bid deadlines
      /** @var EstimateBidDeadlineRepository $estimateBidDeadlineRepo */
      $estimateBidDeadlineRepo = $this->getDoctrine()->getRepository(EstimateBidDeadline::class);
      $bid_deadlines = $estimateBidDeadlineRepo->ListarBidDeadlineDeEstimate($estimate_id);
      foreach ($bid_deadlines as $bid_deadline) {
         $em->remove($bid_deadline);
      }

      // items
      /** @var EstimateQuoteRepository $estimateQuoteRepo */
      $estimateQuoteRepo = $this->getDoctrine()->getRepository(EstimateQuote::class);
      $estimate_items = $estimateQuoteRepo->ListarItemsDeEstimate($estimate_id);
      foreach ($estimate_items as $estimate_item) {
         $em->remove($estimate_item);
      }

      // companys
      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate_id);
      foreach ($companys as $company) {
         $em->remove($company);
      }
   }

   /**
    * ActualizarEstimate: Actuializa los datos del rol en la BD
    * @param int $estimate_id Id
    * @author Marcel
    */
   public function ActualizarEstimate(
      $estimate_id,
      $project_id,
      $name,
      $bidDeadline,
      $county_id,
      $priority,
      $bidNo,
      $workHour,
      $phone,
      $email,
      $stage_id,
      $proposal_type_id,
      $status_id,
      $district_id,
      $project_types_id,
      $estimators_id,
      $bid_deadlines,
      $jobWalk,
      $rfiDueDate,
      $projectStart,
      $projectEnd,
      $submittedDate,
      $awardedDate,
      $lostDate,
      $location,
      $sector,
      $plan_downloading_id,
      $bidDescription,
      $bidInstructions,
      $planLink,
      $quoteReceived,
      $companys
   ) {
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
         $entity->setPriority($priority);
         $entity->setBidNo($bidNo);
         $entity->setWorkHour($workHour);
         $entity->setPhone($phone);
         $entity->setEmail($email);
         $entity->setLocation($location);
         $entity->setSector($sector);

         $entity->setBidDescription($bidDescription);
         $entity->setBidInstructions($bidInstructions);
         $entity->setPlanLink($planLink);
         $entity->setQuoteReceived($quoteReceived);

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

         $entity->setCountyObj(NULL);
         if ($county_id != '') {
            $county = $this->getDoctrine()->getRepository(County::class)
               ->find($county_id);
            $entity->setCountyObj($county);
         }

         $entity->setDistrict(NULL);
         if ($district_id != '') {
            $district = $this->getDoctrine()->getRepository(District::class)
               ->find($district_id);
            $entity->setDistrict($district);
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

         // companys
         $this->SalvarCompanys($entity, $companys);

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
    * SalvarCompanys
    * @param $companys
    * @param Estimate $entity
    * @return void
    */
   public function SalvarCompanys($entity, $companys)
   {
      $em = $this->getDoctrine()->getManager();

      if (!empty($companys)) {
         foreach ($companys as $value) {

            $estimate_company_entity = null;

            if (is_numeric($value->id)) {
               $estimate_company_entity = $this->getDoctrine()->getRepository(EstimateCompany::class)
                  ->find($value->id);
            }

            $is_new_estimate_company = false;
            if ($estimate_company_entity == null) {
               $estimate_company_entity = new EstimateCompany();
               $is_new_estimate_company = true;
            }

            if ($value->company_id != '') {
               $company = $this->getDoctrine()->getRepository(Company::class)
                  ->find($value->company_id);
               $estimate_company_entity->setCompany($company);
            }

            if ($value->contact_id != '') {
               $contact = $this->getDoctrine()->getRepository(CompanyContact::class)
                  ->find($value->contact_id);
               $estimate_company_entity->setContact($contact);
            }

            if ($is_new_estimate_company) {
               $estimate_company_entity->setEstimate($entity);

               $em->persist($estimate_company_entity);
            }
         }
      }
   }

   /**
    * SalvarEstimate: Guarda los datos de estimate en la BD
    * @param string $description Nombre
    * @author Marcel
    */
   public function SalvarEstimate(
      $project_id,
      $name,
      $bidDeadline,
      $county_id,
      $priority,
      $bidNo,
      $workHour,
      $phone,
      $email,
      $stage_id,
      $proposal_type_id,
      $status_id,
      $district_id,
      $project_types_id,
      $estimators_id,
      $bid_deadlines,
      $jobWalk,
      $rfiDueDate,
      $projectStart,
      $projectEnd,
      $submittedDate,
      $awardedDate,
      $lostDate,
      $location,
      $sector,
      $plan_downloading_id,
      $bidDescription,
      $bidInstructions,
      $planLink,
      $quoteReceived,
      $companys
   ) {
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
      $entity->setPriority($priority);
      $entity->setBidNo($bidNo);
      $entity->setWorkHour($workHour);
      $entity->setPhone($phone);
      $entity->setEmail($email);
      $entity->setLocation($location);
      $entity->setSector($sector);

      $entity->setBidDescription($bidDescription);
      $entity->setBidInstructions($bidInstructions);
      $entity->setPlanLink($planLink);
      $entity->setQuoteReceived($quoteReceived);

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

      if ($county_id != '') {
         $county = $this->getDoctrine()->getRepository(County::class)
            ->find($county_id);
         $entity->setCountyObj($county);
      }

      if ($district_id != '') {
         $district = $this->getDoctrine()->getRepository(District::class)
            ->find($district_id);
         $entity->setDistrict($district);
      }

      if ($jobWalk != '') {
         $jobWalk = \DateTime::createFromFormat('m/d/Y H:i', $jobWalk);
         $entity->setJobWalk($jobWalk);
      }

      if ($rfiDueDate != '') {
         $rfiDueDate = \DateTime::createFromFormat('m/d/Y H:i', $rfiDueDate);
         $entity->setRfiDueDate($rfiDueDate);
      }

      if ($projectStart != '') {
         $projectStart = \DateTime::createFromFormat('m/d/Y H:i', $projectStart);
         $entity->setProjectStart($projectStart);
      }

      if ($projectEnd != '') {
         $projectEnd = \DateTime::createFromFormat('m/d/Y H:i', $projectEnd);
         $entity->setProjectEnd($projectEnd);
      }

      if ($submittedDate != '') {
         $submittedDate = \DateTime::createFromFormat('m/d/Y H:i', $submittedDate);
         $entity->setSubmittedDate($submittedDate);
      }

      if ($awardedDate != '') {
         $awardedDate = \DateTime::createFromFormat('m/d/Y H:i', $awardedDate);
         $entity->setAwardedDate($awardedDate);
      }

      if ($lostDate != '') {
         $lostDate = \DateTime::createFromFormat('m/d/Y H:i', $lostDate);
         $entity->setLostDate($lostDate);
      }

      if ($plan_downloading_id != '') {
         $plan_downloading = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->find($plan_downloading_id);
         $entity->setPlanDownloading($plan_downloading);
      }

      $em->persist($entity);

      // save project types
      $this->SalvarProjectTypes($entity, $project_types_id);

      // save estimators
      $this->SalvarEstimators($entity, $estimators_id);

      // bid_deadlines
      $this->SalvarBidDeadlines($entity, $bid_deadlines);

      // companys
      $this->SalvarCompanys($entity, $companys);

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
         /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
         $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
         $estimates_estimators = $estimateEstimatorRepo->ListarUsuariosDeEstimate($entity->getEstimateId());
         foreach ($estimates_estimators as $estimate_estimator) {
            $em->remove($estimate_estimator);
         }
      }

      if ($estimators_id !== '') {

         $estimators_id = explode(',', $estimators_id);

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
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         $estimates_project_types = $estimateProjectTypeRepo->ListarTypesDeEstimate($entity->getEstimateId());
         foreach ($estimates_project_types as $estimate_project_type) {
            $em->remove($estimate_project_type);
         }
      }

      if ($project_types_id !== '') {

         $project_types_id = explode(',', $project_types_id);

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
   public function ListarEstimates(
      $start,
      $limit,
      $sSearch,
      $iSortCol_0,
      $sSortDir_0,
      $stage_id,
      $project_type_id,
      $proposal_type_id,
      $county_id,
      $status_id,
      $district_id,
      $fecha_inicial,
      $fecha_fin
   ) {
      $arreglo_resultado = array();
      $cont = 0;

      // listar
      $lista = [];
      if ($project_type_id === "") {
         /** @var EstimateRepository $estimateRepo */
         $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
         $lista = $estimateRepo->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $stage_id, $proposal_type_id, $county_id, $status_id, $district_id, $fecha_inicial, $fecha_fin);
      } else {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         $estimates_project_type = $estimateProjectTypeRepo->ListarEstimates($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $stage_id, $proposal_type_id, $county_id, $status_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
         foreach ($estimates_project_type as $estimate_project_type) {
            $lista[] = $estimate_project_type->getEstimate();
         }
      }


      foreach ($lista as $value) {
         $estimate_id = $value->getEstimateId();

         $acciones = $this->ListarAcciones($estimate_id);

         $bidDeadline = $value->getBidDeadline() ? $value->getBidDeadline()->format('m/d/Y H:i') : "Not set";


         $project_id = $value->getProjectId();
         $project_number = $project_id;

         $proposal_number = $value->getBidNo();
         // companies
         $companies = $this->ListarCompaniesParaListado($value);

         // estimators
         $estimators = $this->ListarEstimatorsParaListado($estimate_id);

         $county_name = '';
         if (method_exists($value, 'getCountyObj') && $value->getCountyObj()) {
            $county_name = $value->getCountyObj()->getDescription();
         } elseif (method_exists($value, 'getCounty') && $value->getCounty()) {
            $county_name = $value->getCounty();
         }

         // stage
         $stage = $this->DevolverStageParaListado($estimate_id, $value->getStage());

         // name
         $name = $value->getName() . ($value->getQuoteReceived()
            ? ' <i class="fa fa-check-circle" style="color: green;" title="Quote received"></i>'
            : '');

         $arreglo_resultado[$cont] = array(
            "id" => $estimate_id,
            "name" => $name,
            "proposal_number" => $proposal_number,
            "project_id" => $project_number,
            "county" => $county_name,
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

      /** @var EstimateCompanyRepository $estimateCompanyRepo */
      $estimateCompanyRepo = $this->getDoctrine()->getRepository(EstimateCompany::class);
      $estimate_companys = $estimateCompanyRepo->ListarCompanysDeEstimate($estimate->getEstimateId());
      foreach ($estimate_companys as $estimate_company) {
         $companies[] = $estimate_company->getCompany()->getName();
      }

      /** @var EstimateBidDeadlineRepository $estimateBidDeadlineRepo */
      $estimateBidDeadlineRepo = $this->getDoctrine()->getRepository(EstimateBidDeadline::class);
      $lista = $estimateBidDeadlineRepo->ListarBidDeadlineDeEstimate($estimate->getEstimateId());
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
      $primerNombre_truncado = $this->truncate($primerNombre, 30);
      $html = '<div class="d-inline-flex align-items-center" style="gap: 8px;">';

      $restantes = array_slice($companies, 1);

      // Estilo base para los badges
      $estiloBase = 'padding: 3px 9px; font-size: 11px;cursor:pointer; color: #FFF;';

      // Si hay más de una empresa, agregar borde izquierdo rojo al primer badge
      $estiloPrincipal = $estiloBase;
      if (count($restantes) > 0) {
         $estiloPrincipal .= ' border-left: 3px solid red;';
      }

      // Badge principal
      $html .= '<span class="badge badge-primary" style="' . $estiloPrincipal . '" title="' . $primerNombre . '">' . $primerNombre_truncado . '</span>';

      if (count($restantes) > 0) {
         // Badges del popover
         $contenidoPopover = implode('', array_map(function ($c) use ($estiloBase) {
            $c = htmlspecialchars($c, ENT_QUOTES, 'UTF-8');
            return '<div class="mb-1"><span class="badge badge-primary" style="' . $estiloBase . '">' . $c . '</span></div>';
         }, $restantes));

         $dataContent = htmlspecialchars($contenidoPopover, ENT_QUOTES, 'UTF-8');

         $html .= '<span class="badge bg-primary popover-company"
                        data-bs-toggle="popover"
                        data-bs-html="true"
                        data-bs-content="' . $dataContent . '"
                        style="' . $estiloBase . '">+' . count($restantes) . '</span>';
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
      /** @var EstimateEstimatorRepository $estimateEstimatorRepo */
      $estimateEstimatorRepo = $this->getDoctrine()->getRepository(EstimateEstimator::class);
      $lista = $estimateEstimatorRepo->ListarUsuariosDeEstimate($estimate_id);
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
   public function TotalEstimates($sSearch, $stage_id, $project_type_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin)
   {
      if ($project_type_id === '') {
         /** @var EstimateRepository $estimateRepo */
         $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
         return $estimateRepo->TotalEstimates($sSearch, $stage_id, $proposal_type_id, $status_id, $county_id, $district_id, $fecha_inicial, $fecha_fin);
      } else {
         /** @var EstimateProjectTypeRepository $estimateProjectTypeRepo */
         $estimateProjectTypeRepo = $this->getDoctrine()->getRepository(EstimateProjectType::class);
         return $estimateProjectTypeRepo->TotalEstimates($sSearch, $stage_id, $proposal_type_id, $status_id, $county_id, $district_id, $project_type_id, $fecha_inicial, $fecha_fin);
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
            $acciones .= '<a href="javascript:;" class="edit btn btn-icon btn-light-success btn-sm me-1" title="Edit record" data-id="' . $id . '"><i class="la la-edit fs-2"></i></a>';
         } else {

            $acciones .= '<a href="javascript:;" class="edit btn btn-icon btn-light-success btn-sm me-1" title="View record" data-id="' . $id . '"><i class="la la-eye fs-2"></i></a>';
         }

         if ($permiso[0]['eliminar']) {
            $acciones .= '<a href="javascript:;" class="delete btn btn-icon btn-light-danger btn-sm" title="Delete record" data-id="' . $id . '"><i class="la la-trash fs-2"></i></a>';
         }
      }

      return $acciones;
   }
}
