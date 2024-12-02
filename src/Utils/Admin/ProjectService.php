<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\DataTrackingItem;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\Project;
use App\Entity\DataTracking;
use App\Entity\ProjectContact;
use App\Entity\ProjectItem;
use App\Entity\ProjectNotes;
use App\Entity\Unit;
use App\Utils\Base;

class ProjectService extends Base
{

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
    public function AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id)
    {
        $resultado = [];

        $em = $this->getDoctrine()->getManager();

        $project_entity = $this->getDoctrine()->getRepository(Project::class)->find($project_id);
        if ($project_entity != null) {
            $project_item_entity = null;

            if (is_numeric($project_item_id)) {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                    ->find($project_item_id);
            }

            $is_new_project_item = false;
            if ($project_item_entity == null) {
                $project_item_entity = new ProjectItem();
                $is_new_project_item = true;
            }

            $project_item_entity->setYieldCalculation($yield_calculation);
            $project_item_entity->setPrice($price);
            $project_item_entity->setQuantity($quantity);

            $equation_entity = null;
            if ($equation_id != '') {
                $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
                $project_item_entity->setEquation($equation_entity);
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

            $project_item_entity->setItem($item_entity);

            if ($is_new_project_item) {
                $project_item_entity->setProject($project_entity);

                $em->persist($project_item_entity);
            }

            $em->flush();

            $resultado['success'] = true;

            // devolver item
            $item = $this->DevolverItemDeProject($project_item_entity);
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
     * @param int $project_item_id Id
     * @author Marcel
     */
    public function EliminarItem($project_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->find($project_item_id);
        /**@var ProjectItem $entity */
        if ($entity != null) {

            // verificar si se puede eliminar
            /*$se_puede_eliminar = $this->SePuedeEliminarItem($project_item_id);
            if ($se_puede_eliminar != '') {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;
                return $resultado;
            }*/

            // eliminar informacion relacionada
            $this->EliminarInformacionDeProjectItem($project_item_id);

            $item_name = $entity->getItem()->getDescription();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project Item";
            $log_descripcion = "The item: $item_name of the project is deleted";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeProjectItem
     * @param $project_item_id
     * @return void
     */
    private function EliminarInformacionDeProjectItem($project_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        // data tracking
        $data_tracking_items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarDataTrackingsDeItem($project_item_id);
        foreach ($data_tracking_items as $data_tracking_item) {
            $em->remove($data_tracking_item);
        }

        // invoices
        $invoice_items = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarInvoicesDeItem($project_item_id);
        foreach ($invoice_items as $invoice_item) {
            $em->remove($invoice_item);
        }
    }

    /**
     * SePuedeEliminarItem
     * @param $item_id
     * @return string
     */
    private function SePuedeEliminarItem($project_item_id)
    {
        $texto_error = '';

        // data tracking
        $data_tracking = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarDataTrackingsDeItem($project_item_id);
        if (count($data_tracking) > 0) {
            $texto_error = "The item could not be deleted, because it is related to a data tracking";
        }

        // invoices
        $invoices = $this->getDoctrine()->getRepository(InvoiceItem::class)
            ->ListarInvoicesDeItem($project_item_id);
        if (count($invoices) > 0) {
            $texto_error = "The item could not be deleted, because it is related to a invoice";
        }

        return $texto_error;

    }

    /**
     * EliminarNotes: Elimina un notes en la BD
     * @param int $notes_id Id
     * @author Marcel
     */
    public function EliminarNotes($notes_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->find($notes_id);
        /**@var ProjectNotes $entity */
        if ($entity != null) {
            $notes = $entity->getNotes();
            $project_name = $entity->getProject()->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project Notes";
            $log_descripcion = "The notes: $notes is delete from project: $project_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarNotesDate: Elimina un notes en un rango de fechas en la BD
     * @param int $project_id Id
     * @author Marcel
     */
    public function EliminarNotesDate($project_id, $from, $to)
    {
        $em = $this->getDoctrine()->getManager();

        $project_entity = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
        /** @var Project $project_entity */
        if ($project_entity != null) {

            $project_name = $project_entity->getName();


            $notes = $this->getDoctrine()->getRepository(ProjectNotes::class)
                ->ListarNotesDeProject($project_id, $from, $to);
            foreach ($notes as $entity) {
                $em->remove($entity);
            }

            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project Notes";
            $log_descripcion = "The notes $from and $to is delete from project: $project_name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosNotes: Carga los datos de un notes
     *
     * @param int $notes_id Id
     *
     * @author Marcel
     */
    public function CargarDatosNotes($notes_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->find($notes_id);
        /** @var ProjectNotes $entity */
        if ($entity != null) {

            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');

            $resultado['success'] = true;
            $resultado['notes'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * SalvarNotes
     * @param $notes_id
     * @param $project_id
     * @param $notes
     * @param $date
     * @return array
     */
    public function SalvarNotes($notes_id, $project_id, $notes, $date)
    {

        $em = $this->getDoctrine()->getManager();

        $project_entity = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
        /** @var Project $project_entity */
        if ($project_entity != null) {

            $entity = null;
            $is_new = false;

            if (is_numeric($notes_id)) {
                $entity = $this->getDoctrine()->getRepository(ProjectNotes::class)
                    ->find($notes_id);
            }

            if ($entity == null) {
                $entity = new ProjectNotes();
                $is_new = true;
            }

            $entity->setNotes($notes);

            if ($date != '') {
                $date = \DateTime::createFromFormat('m/d/Y', $date);
                $entity->setDate($date);
            }

            $entity->setProject($project_entity);

            $log_operacion = "Add";
            $log_descripcion = "The notes: $notes is add to the project: " . $project_entity->getName();

            if ($is_new) {
                $em->persist($entity);
            } else {
                $log_operacion = "Update";
                $log_descripcion = "The notes: $notes is modified to the project: " . $project_entity->getName();
            }

            $em->flush();

            //Salvar log
            $log_categoria = "Project Notes";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The project not exist.";
        }

        return $resultado;

    }

    /**
     * ListarNotes: Listar los notes
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->ListarNotes($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {
            $notes_id = $value->getId();

            $acciones = $this->ListarAccionesNotes($notes_id);

            $arreglo_resultado[$cont] = array(
                "id" => $notes_id,
                "notes" => $value->getNotes(),
                "date" => $value->getDate()->format('m/d/Y'),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalNotes: Total de notes
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin)
    {
        $total = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->TotalNotes($sSearch, $project_id, $fecha_inicial, $fecha_fin);

        return $total;
    }

    /**
     * ListarAccionesNotes: Lista las acciones
     *
     * @author Marcel
     */
    public function ListarAccionesNotes($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 9);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill" title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
        }

        return $acciones;
    }

    /**
     * ListarOrdenados
     * @param $search
     * @param $company_id
     * @param $inspector_id
     * @return array
     */
    public function ListarOrdenados($search, $company_id, $inspector_id, $from, $to, $status = '')
    {
        $projects = [];

        $lista = $this->getDoctrine()->getRepository(Project::class)
            ->ListarOrdenados($search, $company_id, $inspector_id, $from, $to);
        foreach ($lista as $value) {
            $project_id = $value->getProjectId();

            $is_valid_status = $this->FiltrarProjectPorStatus($project_id, $status);
            if ($is_valid_status) {
                $projects[] = [
                    'project_id' => $project_id,
                    'number' => $value->getProjectNumber(),
                    'name' => $value->getName()
                ];
            }

        }

        return $projects;
    }

    /**
     * FiltrarProjectPorStatus
     * @param $status
     * @return boolean
     */
    private function FiltrarProjectPorStatus($project_id, $status)
    {
        $is_valid = true;

        if ($status != '') {

            $is_valid = false;

            $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
                ->ListarDataTracking($project_id);

            if ($status == 'working' && !empty($data_tracking)) {
                $is_valid = true;
            }
            if ($status == 'notworking' && empty($data_tracking)) {
                $is_valid = true;
            }
        }


        return $is_valid;
    }

    /**
     * ListarItemsParaInvoice
     * @param $project_id
     * @param $fecha_inicial
     * @param $fecha_fin
     * @return array
     */
    public function ListarItemsParaInvoice($project_id, $fecha_inicial, $fecha_fin)
    {
        $items = [];

        // listar items de project
        $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarItemsDeProject($project_id);
        foreach ($project_items as $value) {
            $project_item_id = $value->getId();

            $contract_qty = $value->getQuantity();
            $price = $value->getPrice();
            $contract_amount = $contract_qty * $price;

            $quantity_from_previous = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->TotalPreviousQuantity($project_item_id);

            $quantity = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity("", $project_item_id, $fecha_inicial, $fecha_fin);

            $quantity_completed = $quantity + $quantity_from_previous;

            $amount = $quantity * $price;

            $total_amount = $quantity_completed * $price;

            $items[] = [
                "project_item_id" => $project_item_id,
                "item_id" => $value->getItem()->getItemId(),
                "item" => $value->getItem()->getDescription(),
                "unit" => $value->getItem()->getUnit()->getDescription(),
                "contract_qty" => $contract_qty,
                "price" => $price,
                "contract_amount" => $contract_amount,
                "quantity_from_previous" => $quantity_from_previous,
                "quantity" => $quantity,
                "quantity_completed" => $quantity_completed,
                "amount" => $amount,
                "total_amount" => $total_amount
            ];
        }

        return $items;
    }

    /**
     * CargarDatosProject: Carga los datos de un project
     *
     * @param int $project_id Id
     *
     * @author Marcel
     */
    public function CargarDatosProject($project_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
        /** @var Project $entity */
        if ($entity != null) {

            $arreglo_resultado['company_id'] = $entity->getCompany()->getCompanyId();
            $arreglo_resultado['inspector_id'] = $entity->getInspector() != null ? $entity->getInspector()->getInspectorId() : '';

            $arreglo_resultado['number'] = $entity->getProjectNumber();
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['po_number'] = $entity->getPoNumber();
            $arreglo_resultado['po_cg'] = $entity->getPoCG();
            $arreglo_resultado['manager'] = $entity->getManager();
            $arreglo_resultado['status'] = $entity->getStatus();
            $arreglo_resultado['owner'] = $entity->getOwner();
            $arreglo_resultado['subcontract'] = $entity->getSubcontract();
            $arreglo_resultado['federal_funding'] = $entity->getFederalFunding();
            $arreglo_resultado['county'] = $entity->getCounty();
            $arreglo_resultado['resurfacing'] = $entity->getResurfacing();
            $arreglo_resultado['invoice_contact'] = $entity->getInvoiceContact();
            $arreglo_resultado['certified_payrolls'] = $entity->getCertifiedPayrolls();
            $arreglo_resultado['start_date'] = $entity->getStartDate() != '' ? $entity->getStartDate()->format('m/d/Y') : '';
            $arreglo_resultado['end_date'] = $entity->getEndDate() != '' ? $entity->getEndDate()->format('m/d/Y') : '';
            $arreglo_resultado['due_date'] = $entity->getDueDate() != '' ? $entity->getDueDate()->format('m/d/Y') : '';
            $arreglo_resultado['contract_amount'] = $entity->getContractAmount();
            $arreglo_resultado['proposal_number'] = $entity->getProposalNumber();
            $arreglo_resultado['project_id_number'] = $entity->getProjectIdNumber();

            // items
            $items = $this->ListarItemsDeProject($project_id);
            $arreglo_resultado['items'] = $items;

            // contacts
            $contacts = $this->ListarContacts($project_id);
            $arreglo_resultado['contacts'] = $contacts;

            $resultado['success'] = true;
            $resultado['project'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarContacts
     * @param $project_id
     * @return array
     */
    public function ListarContacts($project_id)
    {
        $contacts = [];

        $project_contacts = $this->getDoctrine()->getRepository(ProjectContact::class)
            ->ListarContacts($project_id);
        foreach ($project_contacts as $key => $contact) {
            $contacts[] = [
                'contact_id' => $contact->getContactId(),
                'name' => $contact->getName(),
                'email' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'role' => $contact->getRole(),
                'notes' => $contact->getNotes(),
                'posicion' => $key
            ];
        }

        return $contacts;
    }

    /**
     * ListarItemsDeProject
     * @param $project_id
     * @return array
     */
    public function ListarItemsDeProject($project_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarItemsDeProject($project_id);
        foreach ($lista as $key => $value) {
            $item = $this->DevolverItemDeProject($value, $key);
            $items[] = $item;
        }

        return $items;
    }

    /**
     * DevolverItemDeProject
     * @param ProjectItem $value
     * @return array
     */
    public function DevolverItemDeProject($value, $key = -1)
    {
        $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

        $quantity = $value->getQuantity();
        $price = $value->getPrice();
        $total = $quantity * $price;

        return [
            'project_item_id' => $value->getId(),
            "item_id" => $value->getItem()->getItemId(),
            "item" => $value->getItem()->getDescription(),
            "unit" => $value->getItem()->getUnit()->getDescription(),
            "quantity" => $quantity,
            "price" => $price,
            "total" => $total,
            "yield_calculation" => $value->getYieldCalculation(),
            "yield_calculation_name" => $yield_calculation_name,
            "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
            "posicion" => $key
        ];
    }

    /**
     * EliminarProject: Elimina un rol en la BD
     * @param int $project_id Id
     * @author Marcel
     */
    public function EliminarProject($project_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
        /**@var Project $entity */
        if ($entity != null) {

            // invoices
            $invoices = $this->getDoctrine()->getRepository(Invoice::class)
                ->ListarInvoicesDeProject($project_id);
            if (count($invoices) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The project could not be deleted, because it is related to a invoice";
                return $resultado;
            }

            // contacts
            $contacts = $this->getDoctrine()->getRepository(ProjectContact::class)
                ->ListarContacts($project_id);
            foreach ($contacts as $contact) {
                $em->remove($contact);
            }

            // items
            $items = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->ListarItemsDeProject($project_id);
            foreach ($items as $item) {
                $em->remove($item);
            }

            // data tracking
            $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
                ->ListarDataTracking($project_id);
            foreach ($data_tracking as $data) {

                $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                    ->ListarItems($data->getId());
                foreach ($items as $item) {
                    $em->remove($item);
                }

                $em->remove($data);
            }

            // notes
            $notes = $this->getDoctrine()->getRepository(ProjectNotes::class)
                ->ListarNotesDeProject($project_id);
            foreach ($notes as $note) {
                $em->remove($note);
            }

            $project_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project";
            $log_descripcion = "The project is deleted: $project_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarProjects: Elimina los projects seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarProjects($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $project_id) {
                if ($project_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Project::class)
                        ->find($project_id);
                    /**@var Project $entity */
                    if ($entity != null) {

                        // invoices
                        $invoices = $this->getDoctrine()->getRepository(Invoice::class)
                            ->ListarInvoicesDeProject($project_id);
                        if (count($invoices) == 0) {

                            // contacts
                            $contacts = $this->getDoctrine()->getRepository(ProjectContact::class)
                                ->ListarContacts($project_id);
                            foreach ($contacts as $contact) {
                                $em->remove($contact);
                            }

                            // items
                            $items = $this->getDoctrine()->getRepository(ProjectItem::class)
                                ->ListarItemsDeProject($project_id);
                            foreach ($items as $item) {
                                $em->remove($item);
                            }

                            // data tracking
                            $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
                                ->ListarDataTracking($project_id);
                            foreach ($data_tracking as $data) {

                                $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                                    ->ListarItems($data->getId());
                                foreach ($items as $item) {
                                    $em->remove($item);
                                }

                                $em->remove($data);
                            }

                            // notes
                            $notes = $this->getDoctrine()->getRepository(ProjectNotes::class)
                                ->ListarNotesDeProject($project_id);
                            foreach ($notes as $note) {
                                $em->remove($note);
                            }

                            $project_descripcion = $entity->getName();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Project";
                            $log_descripcion = "The project is deleted: $project_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The projects could not be deleted, because they are associated with a invoice";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected projects because they are associated with a invoice";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarProject: Actuializa los datos del rol en la BD
     * @param int $project_id Id
     * @author Marcel
     */
    public function ActualizarProject($project_id, $company_id, $inspector_id, $number, $name, $location,
                                      $po_number, $po_cg, $manager, $status, $owner, $subcontract,
                                      $federal_funding, $county, $resurfacing, $invoice_contact,
                                      $certified_payrolls, $start_date, $end_date, $due_date,
                                      $contract_amount, $proposal_number, $project_id_number, $items, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Project::class)
            ->find($project_id);
        /** @var Project $entity */
        if ($entity != null) {
            //Verificar description
            $project = $this->getDoctrine()->getRepository(Project::class)
                ->findOneBy(['projectNumber' => $number]);
            if ($project != null && $entity->getProjectId() != $project->getProjectId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The project number is in use, please try entering another one.";
                return $resultado;
            }

            // para guardar los cambios
            $notas = [];

            if ($number != $entity->getProjectNumber()) {
                $notas[] = [
                    'notes' => 'Change project number, old value: ' . $entity->getProjectNumber(),
                    'date' => new \DateTime()
                ];
            }

            $entity->setProjectNumber($number);


            if ($name != $entity->getName()) {
                $notas[] = [
                    'notes' => 'Change description, old value: ' . $entity->getName(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setName($name);

            if ($location != $entity->getLocation()) {
                $notas[] = [
                    'notes' => 'Change location, old value: ' . $entity->getLocation(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setLocation($location);

            $entity->setPoNumber($po_number);
            $entity->setPoCG($po_cg);

            if ($manager != $entity->getManager()) {
                $notas[] = [
                    'notes' => 'Change manager, old value: ' . $entity->getManager(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setManager($manager);

            if ($status != $entity->getStatus()) {
                // definir el valor del status
                switch ($entity->getStatus()) {
                    case 0:
                        $old_status = "Not Started";
                        break;
                    case 1:
                        $old_status = "In Progress";
                        break;
                    default:
                        $old_status = "Completed";
                        break;
                }

                $notas[] = [
                    'notes' => 'Change status, old value: ' . $old_status,
                    'date' => new \DateTime()
                ];
            }
            $entity->setStatus($status);

            if ($contract_amount != $entity->getContractAmount()) {
                $notas[] = [
                    'notes' => 'Change contract amount, old value: ' . $entity->getContractAmount(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setContractAmount($contract_amount);

            if ($proposal_number != $entity->getProposalNumber()) {
                $notas[] = [
                    'notes' => 'Change proposal id #, old value: ' . $entity->getProposalNumber(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setProposalNumber($proposal_number);


            if ($project_id_number != $entity->getProjectIdNumber()) {
                $notas[] = [
                    'notes' => 'Change project id #, old value: ' . $entity->getProjectIdNumber(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setProjectIdNumber($project_id_number);


            if ($company_id != '') {

                if ($company_id != $entity->getCompany()->getCompanyId()) {
                    $notas[] = [
                        'notes' => 'Change company, old value: ' . $entity->getCompany()->getName(),
                        'date' => new \DateTime()
                    ];
                }

                $company = $this->getDoctrine()->getRepository(Company::class)
                    ->find($company_id);
                $entity->setCompany($company);
            }


            if ($inspector_id != '') {

                if ($inspector_id != $entity->getInspector()->getInspectorId()) {
                    $notas[] = [
                        'notes' => 'Change inspector, old value: ' . $entity->getInspector()->getName(),
                        'date' => new \DateTime()
                    ];
                }

                $inspector = $this->getDoctrine()->getRepository(Inspector::class)
                    ->find($inspector_id);
                $entity->setInspector($inspector);
            }


            if ($owner != $entity->getOwner()) {
                $notas[] = [
                    'notes' => 'Change owner, old value: ' . $entity->getOwner(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setOwner($owner);

            if ($subcontract != $entity->getSubcontract()) {
                $notas[] = [
                    'notes' => 'Change Subcontract NO, old value: ' . $entity->getSubcontract(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setSubcontract($subcontract);

            if ($federal_funding != $entity->getFederalFunding()) {
                $notas[] = [
                    'notes' => 'Change federal funding, old value: ' . $entity->getFederalFunding() ? 'Yes' : 'No',
                    'date' => new \DateTime()
                ];
            }
            $entity->setFederalFunding($federal_funding);

            if ($county != $entity->getCounty()) {
                $notas[] = [
                    'notes' => 'Change county, old value: ' . $entity->getCounty(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setCounty($county);

            if ($resurfacing != $entity->getResurfacing()) {
                $notas[] = [
                    'notes' => 'Change resurfacing, old value: ' . $entity->getResurfacing() ? 'Yes' : 'No',
                    'date' => new \DateTime()
                ];
            }
            $entity->setResurfacing($resurfacing);

            if ($invoice_contact != $entity->getInvoiceContact()) {
                $notas[] = [
                    'notes' => 'Change invoice contact, old value: ' . $entity->getInvoiceContact(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setInvoiceContact($invoice_contact);

            if ($certified_payrolls != $entity->getCertifiedPayrolls()) {
                $notas[] = [
                    'notes' => 'Change certified payrolls, old value: ' . $entity->getCertifiedPayrolls() ? 'Yes' : 'No',
                    'date' => new \DateTime()
                ];
            }
            $entity->setCertifiedPayrolls($certified_payrolls);

            if ($start_date != '') {

                if ($start_date != $entity->getStartDate()->format('m/d/Y')) {
                    $notas[] = [
                        'notes' => 'Change start date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $entity->getStartDate()->format('m/d/Y')),
                        'date' => new \DateTime()
                    ];
                }


                $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
                $entity->setStartDate($start_date);
            }

            if ($end_date != '') {

                if ($end_date != $entity->getEndDate()->format('m/d/Y')) {
                    $notas[] = [
                        'notes' => 'Change end date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $entity->getEndDate()->format('m/d/Y')),
                        'date' => new \DateTime()
                    ];
                }

                $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
                $entity->setEndDate($end_date);
            }

            if ($due_date != '') {

                if ($due_date != $entity->getDueDate()->format('m/d/Y')) {
                    $notas[] = [
                        'notes' => 'Change due date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $entity->getDueDate()->format('m/d/Y')),
                        'date' => new \DateTime()
                    ];
                }

                $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
                $entity->setDueDate($due_date);
            }

            $entity->setUpdatedAt(new \DateTime());

            // items
            $items_new = $this->SalvarItems($entity, $items);
            // save contacts
            $this->SalvarContacts($entity, $contacts);

            // save notes
            $this->SalvarNotesUpdate($entity, $notas);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Project";
            $log_descripcion = "The project is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['project_id'] = $project_id;
            $resultado['items'] = $items_new;

            return $resultado;
        }
    }

    /**
     * SalvarNotesUpdate
     * @param $notas
     * @param Project $entity
     * @return void
     */
    public function SalvarNotesUpdate($entity, $notas)
    {
        $em = $this->getDoctrine()->getManager();

        //notes
        foreach ($notas as $value) {

            $project_note = new ProjectNotes();

            $project_note->setNotes($value['notes']);
            $project_note->setDate($value['date']);

            $project_note->setProject($entity);

            $em->persist($project_note);

        }
    }

    /**
     * SalvarProject: Guarda los datos de project en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarProject($company_id, $inspector_id, $number, $name, $location,
                                  $po_number, $po_cg, $manager, $status, $owner, $subcontract,
                                  $federal_funding, $county, $resurfacing, $invoice_contact,
                                  $certified_payrolls, $start_date, $end_date, $due_date,
                                  $contract_amount, $proposal_number, $project_id_number, $items, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar number
        $project = $this->getDoctrine()->getRepository(Project::class)
            ->findOneBy(['projectNumber' => $number]);
        if ($project != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The project number is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Project();

        $entity->setProjectNumber($number);
        $entity->setName($name);
        $entity->setLocation($location);
        $entity->setPoNumber($po_number);
        $entity->setPoCG($po_cg);
        $entity->setManager($manager);
        $entity->setStatus($status);
        $entity->setContractAmount($contract_amount);
        $entity->setProposalNumber($proposal_number);
        $entity->setProjectIdNumber($project_id_number);

        if ($company_id != '') {
            $company = $this->getDoctrine()->getRepository(Company::class)
                ->find($company_id);
            $entity->setCompany($company);
        }
        if ($inspector_id != '') {
            $inspector = $this->getDoctrine()->getRepository(Inspector::class)
                ->find($inspector_id);
            $entity->setInspector($inspector);
        }

        $entity->setOwner($owner);
        $entity->setSubcontract($subcontract);
        $entity->setFederalFunding($federal_funding);
        $entity->setCounty($county);
        $entity->setResurfacing($resurfacing);
        $entity->setInvoiceContact($invoice_contact);
        $entity->setCertifiedPayrolls($certified_payrolls);

        if ($start_date != '') {
            $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
            $entity->setStartDate($start_date);
        }

        if ($end_date != '') {
            $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
            $entity->setEndDate($end_date);
        }

        if ($due_date != '') {
            $due_date = \DateTime::createFromFormat('m/d/Y', $due_date);
            $entity->setDueDate($due_date);
        }

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        // items
        $items_new = $this->SalvarItems($entity, $items);

        // save contacts
        $this->SalvarContacts($entity, $contacts);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Project";
        $log_descripcion = "The project is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['project_id'] = $entity->getProjectId();
        $resultado['items'] = $items_new;

        return $resultado;
    }

    /**
     * SalvarContacts
     * @param $contacts
     * @param Project $entity
     * @return void
     */
    public function SalvarContacts($entity, $contacts)
    {
        $em = $this->getDoctrine()->getManager();

        //Senderos
        foreach ($contacts as $value) {

            $contact_entity = null;

            if (is_numeric($value->contact_id)) {
                $contact_entity = $this->getDoctrine()->getRepository(ProjectContact::class)
                    ->find($value->contact_id);
            }

            $is_new_contact = false;
            if ($contact_entity == null) {
                $contact_entity = new ProjectContact();
                $is_new_contact = true;
            }

            $contact_entity->setName($value->name);
            $contact_entity->setEmail($value->email);
            $contact_entity->setPhone($value->phone);
            $contact_entity->setRole($value->role);
            $contact_entity->setNotes($value->notes);

            if ($is_new_contact) {
                $contact_entity->setProject($entity);

                $em->persist($contact_entity);
            }
        }
    }

    /**
     * SalvarItems
     * @param array $items
     * @param Project $entity
     * @return array
     */
    public function SalvarItems($entity, $items)
    {
        $em = $this->getDoctrine()->getManager();

        // para devolver los items nuevos que se creen
        $items_news = [];

        //Senderos
        foreach ($items as $value) {

            $project_item_entity = null;

            if (is_numeric($value->project_item_id)) {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                    ->find($value->project_item_id);
            }

            $is_new_project_item = false;
            if ($project_item_entity == null) {
                $project_item_entity = new ProjectItem();
                $is_new_project_item = true;
            }

            $project_item_entity->setYieldCalculation($value->yield_calculation);
            $project_item_entity->setPrice($value->price);
            $project_item_entity->setQuantity($value->quantity);

            $equation_entity = null;
            if ($value->equation_id != '') {
                $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($value->equation_id);
                $project_item_entity->setEquation($equation_entity);
            }

            $item_entity = null;
            if ($value->item_id != '') {
                $item_entity = $this->getDoctrine()->getRepository(Item::class)->find($value->item_id);
            } else {
                // add new item
                $item_entity = $this->AgregarNewItem($value, $equation_entity);
                $items_news[] = [
                    'item_id' => $item_entity->getItemId(),
                    'description' => $value->item,
                    'price' => $value->price,
                    'unit' => $value->unit,
                    'equation' => $value->equation_id,
                    'yield' => $value->yield_calculation
                ];
            }

            $project_item_entity->setItem($item_entity);

            if ($is_new_project_item) {
                $project_item_entity->setProject($entity);

                $em->persist($project_item_entity);
            }
        }

        return $items_news;
    }

    /**
     * AgregarNewItem
     * @param $value
     * @return Item
     */
    private function AgregarNewItem($value, $equation_entity)
    {
        $em = $this->getDoctrine()->getManager();

        $item_entity = new Item();

        $item_entity->setDescription($value->item);
        $item_entity->setPrice($value->price);
        $item_entity->setStatus(1);
        $item_entity->setYieldCalculation($value->yield_calculation);

        if ($value->unit_id != '') {
            $unit = $this->getDoctrine()->getRepository(Unit::class)->find($value->unit_id);
            $item_entity->setUnit($unit);
        }

        $item_entity->setEquation($equation_entity);

        $item_entity->setCreatedAt(new \DateTime());

        $em->persist($item_entity);

        $em->flush();

        return $item_entity;
    }


    /**
     * ListarProjects: Listar los projects
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                                   $company_id, $status, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $projects = [];

        if ($sSearch != '') {
            $lista = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                    $company_id, '', $status, $fecha_inicial, $fecha_fin);
            foreach ($lista as $p_i) {
                $projects[] = $p_i->getProject();
            }
        } else {
            $projects = $this->getDoctrine()->getRepository(Project::class)
                ->ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                    $company_id, '', $status, $fecha_inicial, $fecha_fin);
        }

        foreach ($projects as $value) {
            $project_id = $value->getProjectId();

            $acciones = $this->ListarAcciones($project_id);

            // listar ultima nota del proyecto
            $nota = $this->ListarUltimaNotaDeProject($project_id);

            $arreglo_resultado[$cont] = array(
                "id" => $project_id,
                "projectNumber" => $value->getProjectNumber(),
                "name" => $value->getName(),
                "company" => $value->getCompany()->getName(),
                "county" => $value->getCounty(),
                "status" => $value->getStatus(),
                "startDate" => $value->getStartDate() != '' ? $value->getStartDate()->format('m/d/Y') : '',
                "endDate" => $value->getEndDate() != '' ? $value->getEndDate()->format('m/d/Y') : '',
                "dueDate" => $value->getDueDate() != '' ? $value->getDueDate()->format('m/d/Y') : '',
                'nota' => $nota,
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalProjects: Total de projects
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalProjects($sSearch, $company_id, $status, $fecha_inicial, $fecha_fin)
    {
        if ($sSearch != '') {
            $total = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin);
        } else {
            $total = $this->getDoctrine()->getRepository(Project::class)
                ->TotalProjects($sSearch, $company_id, '', $status, $fecha_inicial, $fecha_fin);
        }


        return $total;
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 9);

        $acciones = "";

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