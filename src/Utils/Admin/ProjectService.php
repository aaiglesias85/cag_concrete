<?php

namespace App\Utils\Admin;

use App\Entity\Company;
use App\Entity\County;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\DataTrackingSubcontract;
use App\Entity\Equation;
use App\Entity\Inspector;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\DataTracking;
use App\Entity\ProjectAttachment;
use App\Entity\ProjectContact;
use App\Entity\ProjectItem;
use App\Entity\ProjectNotes;
use App\Entity\ProjectPriceAdjustment;
use App\Entity\Unit;
use App\Utils\Base;

class ProjectService extends Base
{

    /**
     * EliminarArchivo: Elimina un archivo en la BD
     *
     * @param $archivo
     * @return array
     */
    public function EliminarArchivo($archivo)
    {
        $resultado = array();

        //Eliminar archivo
        $dir = 'uploads/project/';
        if (is_file($dir . $archivo)) {
            unlink($dir . $archivo);
        }

        $em = $this->getDoctrine()->getManager();

        $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
            ->findOneBy(array('file' => $archivo));
        if ($archivo_entity != null) {
            $em->remove($archivo_entity);
        }

        $em->flush();

        $resultado['success'] = true;
        return $resultado;
    }

    /**
     * EliminarAjustePrecio: Elimina un ajuste de precio en la BD
     * @param int $id Id
     * @author Marcel
     */
    public function EliminarAjustePrecio($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
            ->find($id);
        /**@var ProjectPriceAdjustment $entity */
        if ($entity != null) {

            $project = $entity->getProject()->getProjectNumber();
            $day = $entity->getDay()->format('m/d/Y');
            $percent = $entity->getPercent();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project Price Adjustment";
            $log_descripcion = "The project price adjustment is deleted: Project #: $project, Day: $day, Percent: $percent";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * ListarDataTrackings: Listar los items details
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending)
    {
        $arreglo_resultado = array();

        $lista = $this->getDoctrine()->getRepository(DataTracking::class)
            ->ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin, $pending);

        foreach ($lista as $value) {
            $data_tracking_id = $value->getId();

            $acciones = $this->ListarAccionesDataTracking($data_tracking_id);

            // conc vendor
            $total_conc_used = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcUsed($data_tracking_id);

            $total_concrete_yiel = $this->CalcularTotalConcreteYiel($data_tracking_id);

            $lost_concrete = round($total_conc_used - $total_concrete_yiel, 2);

            // totales

            /*$total_quantity_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity($data_tracking_id);*/
            $total_quantity_today = $total_conc_used;

            $total_daily_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalDaily($data_tracking_id);

            $total_subcontract = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
                ->TotalPrice($data_tracking_id);

            $total_daily_today = $total_daily_today - $total_subcontract;


            // concrete used price
            $total_concrete = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcPrice($data_tracking_id);


            $total_labor = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                ->TotalLabor($data_tracking_id);

            $total_material = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
                ->TotalMaterials($data_tracking_id);

            $total_people = $value->getTotalPeople();
            $overhead_price = $value->getOverheadPrice();
            $total_overhead = $total_people * $overhead_price;

            // "Labor Total" is the sum of Labor and Overhead Totals
            $total_labor = $total_labor + $total_overhead;

            $profit = $total_daily_today - ($total_concrete + $total_labor + $total_material);

            // color
            $color_used = $value->getColorUsed();
            $color_price = $value->getColorPrice();
            $total_color = $color_used * $color_price;

            $pending = $value->getPending() ? 1 : 0;

            $leads = $this->ListarLeadsDeDataTracking($data_tracking_id);

            $arreglo_resultado[] = [
                "id" => $data_tracking_id,
                'project' => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getDescription(),
                'date' => $value->getDate()->format('m/d/Y'),
                "stationNumber" => $value->getStationNumber(),
                "measuredBy" => $value->getMeasuredBy(),
                "totalConcUsed" => $total_conc_used,
                "lostConcrete" => $lost_concrete,
                "concVendor" => $value->getConcVendor(),
                "concPrice" => $value->getConcPrice(),
                "inspector" => $value->getInspector() != null ? $value->getInspector()->getName() : '',
                "inspectorNumber" => $value->getInspector() != null ? $value->getInspector()->getPhone() : '',
                "crewLead" => $value->getCrewLead(),
                "notes" => $value->getNotes(),
                "totalLabor" => $total_labor,
                "totalMaterial" => $total_material,
                "totalStamps" => $value->getTotalStamps(),
                "otherMaterials" => $value->getOtherMaterials(),
                "leads" => $leads,
                // overhead
                "totalPeople" => $total_people,
                "overheadPrice" => $overhead_price,
                "totalOverhead" => $total_overhead,
                // color
                "colorUsed" => $color_used,
                "colorPrice" => $color_price,
                "totalColor" => $total_color,
                // totales
                "total_concrete_yiel" => $total_concrete_yiel,
                'total_quantity_today' => $total_quantity_today != null ? $total_quantity_today : 0,
                'total_daily_today' => $total_daily_today,
                'total_concrete' => $total_concrete,
                'profit' => $profit,
                'pending' => $pending,
                'acciones' => $acciones
            ];
        }

        return $arreglo_resultado;
    }

    /**
     * ListarLeadsDeDataTracking
     * @param $data_tracking_id
     * @return array
     */
    private function ListarLeadsDeDataTracking($data_tracking_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarLabor($data_tracking_id);
        foreach ($lista as $key => $value) {

            if ($value->getRole() === 'Lead' && ($value->getEmployee() !== null || $value->getEmployeeSubcontractor() !== null)) {
                $employee_name = $value->getEmployee() !== null ? $value->getEmployee()->getName() : $value->getEmployeeSubcontractor()->getName();
                $items[] = $employee_name;
            }
        }

        return implode(",", $items);
    }

    /**
     * TotalDataTrackings: Total de items
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin, $pending)
    {
        $total = $this->getDoctrine()->getRepository(DataTracking::class)
            ->TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin, $pending);

        return $total;
    }

    /**
     * ListarAccionesDataTracking: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAccionesDataTracking($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 10);

        $acciones = '<a href="javascript:;" class="view m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones = '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            }
        }

        return $acciones;
    }

    /**
     * ListarEmployees
     * @param $project_id
     * @return array
     */
    public function ListarEmployees($project_id)
    {
        $employees = [];

        $project_employees = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarEmployeesDeProject($project_id);

        foreach ($project_employees as $key => $project_employee) {
            $value = $project_employee->getEmployee();

            $employees[] = [
                "employee_id" => $value->getEmployeeId(),
                "name" => $value->getName(),
                'posicion' => $key
            ];

        }

        return $employees;
    }

    /**
     * ListarSubcontractors
     * @param $project_id
     * @return array
     */
    public function ListarSubcontractors($project_id)
    {
        $subcontractors = [];

        $project_subcontractors = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
            ->ListarSubcontractorsDeProject($project_id);

        foreach ($project_subcontractors as $key => $project_subcontractor) {
            $value = $project_subcontractor->getSubcontractor();
            if ($value) {
                $subcontractors[] = [
                    "subcontractor_id" => $value->getSubcontractorId(),
                    "name" => $value->getName(),
                    "phone" => $value->getPhone(),
                    "address" => $value->getAddress(),
                    "contactName" => $value->getContactName(),
                    "contactEmail" => $value->getContactEmail(),
                    "companyName" => $value->getCompanyName(),
                    "companyPhone" => $value->getCompanyPhone(),
                    "companyAddress" => $value->getCompanyAddress(),
                    'posicion' => $key
                ];
            }

        }

        return $subcontractors;
    }

    /**
     * EliminarContact: Elimina un contact en la BD
     * @param int $contact_id Id
     * @author Marcel
     */
    public function EliminarContact($contact_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectContact::class)
            ->find($contact_id);
        /**@var ProjectContact $entity */
        if ($entity != null) {

            $contact_name = $entity->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Contact";
            $log_descripcion = "The project contact is deleted: $contact_name";
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
    public function AgregarItem($project_item_id, $project_id, $item_id, $item_name, $unit_id, $quantity, $price, $yield_calculation, $equation_id)
    {
        $resultado = [];

        $em = $this->getDoctrine()->getManager();

        // validar si existe
        if ($item_id !== '') {
            $project_item = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->BuscarItemProject($project_id, $item_id, $price);
            if (!empty($project_item) && $project_item_id != $project_item[0]->getId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The item already exists in the project";
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


        $project_entity = $this->getDoctrine()->getRepository(Project::class)->find($project_id);
        if ($project_entity != null) {
            // para las notas
            $notas = [];

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

            $price_old = $project_item_entity->getPrice();
            $project_item_entity->setPrice($price);

            $quantity_old = $project_item_entity->getQuantity();
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

            $item_description = $item_entity->getDescription();
            $project_item_entity->setItem($item_entity);

            if ($is_new_project_item) {

                // marcar principal
                $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
                    ->BuscarItemProject($project_id, $item_id);
                $principal = empty($project_items) ? true : false;
                $project_item_entity->setPrincipal($principal);

                $project_item_entity->setProject($project_entity);

                $em->persist($project_item_entity);

                // registrar nota
                $notas[] = [
                    'notes' => "Add New Item: {$item_description}",
                    'date' => new \DateTime()
                ];

            } else {

                // change price
                if ($price_old != $price) {

                    $project_item_entity->setPriceOld($price_old);

                    $notas[] = [
                        'notes' => "Change Price Item: {$item_description}, Previous Price: {$price_old}, New Price: {$price}",
                        'date' => new \DateTime()
                    ];
                }

                // change quantity
                if ($quantity_old != $quantity) {

                    $project_item_entity->setQuantityOld($quantity_old);

                    $notas[] = [
                        'notes' => "Change Quantity Item: {$item_description}, Previous Quantity: {$quantity_old}, New Quantity: {$quantity}",
                        'date' => new \DateTime()
                    ];
                }
            }

            $this->SalvarNotesUpdate($project_entity, $notas);

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

        // subcontractors
        $data_tracking_subcontractors = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
            ->ListarSubcontractsDeItemProject($project_item_id);
        foreach ($data_tracking_subcontractors as $data_tracking_subcontractor) {
            $em->remove($data_tracking_subcontractor);
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

            $notes = $value->getNotes();
            $notes = mb_convert_encoding($notes, 'UTF-8', 'UTF-8');

            $arreglo_resultado[$cont] = array(
                "id" => $notes_id,
                "notes" => $notes,
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
    public function ListarOrdenados($search = '', $company_id = '', $inspector_id = '', $from = '', $to = '', $status = '')
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
                    'name' => $value->getName(),
                    'description' => $value->getDescription(),
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

            $unpaid_from_previous = $this->CalcularUnpaidQuantityFromPreviusInvoice($project_item_id);

            $paid_amount_total = $this->CalculaPaidAmountTotalFromPreviusInvoice($project_item_id);

            $items[] = [
                "project_item_id" => $project_item_id,
                "item_id" => $value->getItem()->getItemId(),
                "item" => $value->getItem()->getDescription(),
                "unit" => $value->getItem()->getUnit()->getDescription(),
                "contract_qty" => $contract_qty,
                "price" => $price,
                "contract_amount" => $contract_amount,
                "quantity_from_previous" => $quantity_from_previous ?? 0,
                "unpaid_from_previous" => $unpaid_from_previous,
                "quantity" => $quantity ?? 0,
                "quantity_completed" => $quantity_completed,
                "amount" => $amount,
                "total_amount" => $total_amount,
                "paid_amount_total" => $paid_amount_total,
                "principal" => $value->getPrincipal()
            ];
        }

        return $items;
    }

    /**
     * CalculaPaidAmountTotalFromPreviusInvoice
     * @param $project_item_id
     * @return void
     */
    public function CalculaPaidAmountTotalFromPreviusInvoice($project_item_id)
    {
        $total = 0;

        $invoice_items = $this->getDoctrine()->getRepository(InvoiceItem::class)->ListarInvoicesDeItem($project_item_id);
        foreach ($invoice_items as $value) {
            $total += $value->getPaidAmount();
        }

        return $total;
    }

    /**
     * CalcularUnpaidQuantityFromPreviusInvoice
     * @param $project_item_id
     * @return void
     */
    public function CalcularUnpaidQuantityFromPreviusInvoice($project_item_id)
    {
        $unpaid_quantity = 0;

        $invoice_items = $this->getDoctrine()->getRepository(InvoiceItem::class)->ListarInvoicesDeItem($project_item_id);
        foreach ($invoice_items as $value) {
            $quantity = $value->getQuantity();
            $paid_quantity = $value->getPaidQty();

            $unpaid_quantity += $quantity - $paid_quantity;
        }


        return $unpaid_quantity;
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
            $arreglo_resultado['company'] = $entity->getCompany()->getName();
            $arreglo_resultado['inspector_id'] = $entity->getInspector() != null ? $entity->getInspector()->getInspectorId() : '';
            $arreglo_resultado['inspector'] = $entity->getInspector() != null ? $entity->getInspector()->getName() : '';
            $arreglo_resultado['county_id'] = $entity->getCountyObj() != null ? $entity->getCountyObj()->getCountyId() : '';
            $arreglo_resultado['county'] = $entity->getCountyObj() != null ? $entity->getCountyObj()->getDescription() : '';

            $arreglo_resultado['number'] = $entity->getProjectNumber();
            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['location'] = $entity->getLocation();
            $arreglo_resultado['po_number'] = $entity->getPoNumber();
            $arreglo_resultado['po_cg'] = $entity->getPoCG();
            $arreglo_resultado['manager'] = $entity->getManager();
            $arreglo_resultado['status'] = $entity->getStatus();
            $arreglo_resultado['owner'] = $entity->getOwner();
            $arreglo_resultado['subcontract'] = $entity->getSubcontract();
            $arreglo_resultado['federal_funding'] = $entity->getFederalFunding();
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
            $contacts = $this->ListarContactsDeProject($project_id);
            $arreglo_resultado['contacts'] = $contacts;

            // ajustes precio
            $ajustes_precio = $this->ListarAjustesPrecioDeProject($project_id);
            $arreglo_resultado['ajustes_precio'] = $ajustes_precio;

            // invoices
            $invoices = $this->ListarInvoicesDeProject($project_id);
            $arreglo_resultado['invoices'] = $invoices;

            // archivos
            $archivos = $this->ListarArchivosDeProject($project_id);
            $arreglo_resultado['archivos'] = $archivos;

            $resultado['success'] = true;
            $resultado['project'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarArchivosDeProject
     * @param $project_id
     * @return array
     */
    public function ListarArchivosDeProject($project_id)
    {
        $archivos = [];

        $project_archivos = $this->getDoctrine()->getRepository(ProjectAttachment::class)
            ->ListarAttachmentsDeProject($project_id);
        foreach ($project_archivos as $key => $project_archivo) {
            $archivos[] = [
                'id' => $project_archivo->getId(),
                'name' => $project_archivo->getName(),
                'file' => $project_archivo->getFile(),
                'posicion' => $key
            ];
        }

        return $archivos;
    }

    /**
     * ListarAjustesPrecioDeProject
     * @param $project_id
     * @return array
     */
    public function ListarAjustesPrecioDeProject($project_id)
    {
        $ajustes = [];

        $project_ajustes = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
            ->ListarAjustesDeProject($project_id);
        foreach ($project_ajustes as $key => $project_ajuste) {
            $ajustes[] = [
                'id' => $project_ajuste->getId(),
                'day' => $project_ajuste->getDay()->format('m/d/Y'),
                'percent' => $project_ajuste->getPercent(),
                'posicion' => $key
            ];
        }

        return $ajustes;
    }

    /**
     * ListarInvoicesDeProject
     * @param $project_id
     * @return array
     */
    public function ListarInvoicesDeProject($project_id)
    {
        $invoices = [];

        $lista = $this->getDoctrine()->getRepository(Invoice::class)
            ->ListarInvoicesDeProject($project_id);
        foreach ($lista as $key => $value) {

            $invoice_id = $value->getInvoiceId();

            $total = $this->getDoctrine()->getRepository(InvoiceItem::class)
                ->TotalInvoice($invoice_id);

            $invoice = [
                "invoice_id" => $invoice_id,
                "number" => $value->getNumber(),
                "company" => $value->getProject()->getCompany()->getName(),
                "project" => $value->getProject()->getName(),
                "startDate" => $value->getStartDate()->format('m/d/Y'),
                "endDate" => $value->getEndDate()->format('m/d/Y'),
                "notes" => $this->truncate($value->getNotes(), 50),
                "total" => number_format($total, 2, '.', ','),
                "createdAt" => $value->getCreatedAt()->format('m/d/Y'),
                "paid" => $value->getPaid() ? 1 : 0,
                "posicion" => $key
            ];
            $invoices[] = $invoice;
        }

        return $invoices;
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
            "quantity_old" => $value->getQuantityOld() ?? '',
            "price" => $price,
            "price_old" => $value->getPriceOld() ?? '',
            "total" => $total,
            "yield_calculation" => $value->getYieldCalculation(),
            "yield_calculation_name" => $yield_calculation_name,
            "equation_id" => $value->getEquation() != null ? $value->getEquation()->getEquationId() : '',
            "principal" => $value->getPrincipal(),
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

            // eliminar informacion de un project
            $this->EliminarInformacionDeProject($project_id);

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

    private function EliminarInformacionDeProject($project_id)
    {
        $em = $this->getDoctrine()->getManager();

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

            // subcontractors
            $data_tracking_subcontractors = $this->getDoctrine()->getRepository(DataTrackingSubcontract::class)
                ->ListarSubcontractsDeItemProject($item->getId());
            foreach ($data_tracking_subcontractors as $subcontractor) {
                $em->remove($subcontractor);
            }

            $em->remove($item);
        }

        // data tracking
        $data_tracking = $this->getDoctrine()->getRepository(DataTracking::class)
            ->ListarDataTracking($project_id);
        foreach ($data_tracking as $data) {

            // eliminar informacion data tracking
            $this->EliminarInformacionRelacionadaDataTracking($data->getId());

            $em->remove($data);
        }

        // notes
        $notes = $this->getDoctrine()->getRepository(ProjectNotes::class)
            ->ListarNotesDeProject($project_id);
        foreach ($notes as $note) {
            $em->remove($note);
        }

        // notificaciones
        $notificaciones = $this->getDoctrine()->getRepository(Notification::class)
            ->ListarNotificacionesDeProject($project_id);
        foreach ($notificaciones as $notificacion) {
            $em->remove($notificacion);
        }

        // prices adjuments
        $ajustes_precio = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
            ->ListarAjustesDeProject($project_id);
        foreach ($ajustes_precio as $ajuste_precio) {
            $em->remove($ajuste_precio);
        }

        // attachments
        $dir = 'uploads/project/';
        $attachments = $this->getDoctrine()->getRepository(ProjectAttachment::class)
            ->ListarAttachmentsDeProject($project_id);
        foreach ($attachments as $attachment) {

            //eliminar archivo
            $file_eliminar = $attachment->getFile();
            if ($file_eliminar != "" && is_file($dir . $file_eliminar)) {
                unlink($dir . $file_eliminar);
            }

            $em->remove($attachment);
        }
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

                            // eliminar informacion de un project
                            $this->EliminarInformacionDeProject($project_id);

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
    public function ActualizarProject($project_id, $company_id, $inspector_id, $number, $name, $description, $location,
                                      $po_number, $po_cg, $manager, $status, $owner, $subcontract,
                                      $federal_funding, $county_id, $resurfacing, $invoice_contact,
                                      $certified_payrolls, $start_date, $end_date, $due_date,
                                      $contract_amount, $proposal_number, $project_id_number,
                                      $items, $contacts, $ajustes_precio, $archivos)
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
                    'notes' => 'Change name, old value: ' . $entity->getName(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setName($name);

            if ($description != $entity->getDescription()) {
                $notas[] = [
                    'notes' => 'Change description, old value: ' . $entity->getDescription(),
                    'date' => new \DateTime()
                ];
            }
            $entity->setDescription($description);

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

            // county
            $county_id_old = $entity->getCountyObj() ? $entity->getCountyObj()->getCountyId() : "";
            $county_descripcion_old = $entity->getCountyObj() ? $entity->getCountyObj()->getDescription() : "";
            if ($county_id != '') {

                if ($county_id != $county_id_old) {
                    $notas[] = [
                        'notes' => 'Change county, old value: ' . $county_descripcion_old,
                        'date' => new \DateTime()
                    ];
                }

                $county = $this->getDoctrine()->getRepository(County::class)
                    ->find($county_id);
                $entity->setCountyObj($county);
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


            // start date
            $start_date_old = $entity->getStartDate() != '' ? $entity->getStartDate()->format('m/d/Y') : '';
            if ($start_date != '') {

                if ($start_date != $start_date_old) {
                    $notas[] = [
                        'notes' => 'Change start date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $start_date_old),
                        'date' => new \DateTime()
                    ];
                }


                $start_date = \DateTime::createFromFormat('m/d/Y', $start_date);
                $entity->setStartDate($start_date);
            }


            // end date
            $end_date_old = $entity->getEndDate() != '' ? $entity->getEndDate()->format('m/d/Y') : '';
            if ($end_date != '') {

                if ($end_date != $end_date_old) {
                    $notas[] = [
                        'notes' => 'Change end date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $end_date_old),
                        'date' => new \DateTime()
                    ];
                }

                $end_date = \DateTime::createFromFormat('m/d/Y', $end_date);
                $entity->setEndDate($end_date);
            }

            // due date
            $due_date_old = $entity->getDueDate() != '' ? $entity->getDueDate()->format('m/d/Y') : '';
            $entity->setDueDate(NULL);
            if ($due_date != '') {
                if ($due_date != $due_date_old) {
                    $notas[] = [
                        'notes' => 'Change due date, old value: ' . preg_replace('/\/00(\d{2})$/', '/20$1', $due_date_old),
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
            // save ajustes de precio
            $this->SalvarAjustesPrecio($entity, $ajustes_precio);
            // save archivos
            $this->SalvarArchivos($entity, $archivos);

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
     * SalvarProject: Guarda los datos de project en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarProject($company_id, $inspector_id, $number, $name, $description, $location,
                                  $po_number, $po_cg, $manager, $status, $owner, $subcontract,
                                  $federal_funding, $county_id, $resurfacing, $invoice_contact,
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
        $entity->setDescription($description);
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

        if ($county_id !== "") {
            $county = $this->getDoctrine()->getRepository(County::class)
                ->find($county_id);
            $entity->setCountyObj($county);
        }

        $entity->setOwner($owner);
        $entity->setSubcontract($subcontract);
        $entity->setFederalFunding($federal_funding);
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
     * SalvarArchivos
     * @param $archivos
     * @param Project $entity
     * @return void
     */
    public function SalvarArchivos($entity, $archivos)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($archivos as $value) {

            $archivo_entity = null;

            if (is_numeric($value->id)) {
                $archivo_entity = $this->getDoctrine()->getRepository(ProjectAttachment::class)
                    ->find($value->id);
            }

            $is_new_archivo = false;
            if ($archivo_entity == null) {
                $archivo_entity = new ProjectAttachment();
                $is_new_archivo = true;
            }

            $archivo_entity->setName($value->name);
            $archivo_entity->setFile($value->file);

            if ($is_new_archivo) {
                $archivo_entity->setProject($entity);

                $em->persist($archivo_entity);
            }
        }
    }

    /**
     * SalvarAjustesPrecio
     * @param $ajustes_precio
     * @param Project $entity
     * @return void
     */
    public function SalvarAjustesPrecio($entity, $ajustes_precio)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($ajustes_precio as $value) {

            $ajuste_entity = null;

            if (is_numeric($value->id)) {
                $ajuste_entity = $this->getDoctrine()->getRepository(ProjectPriceAdjustment::class)
                    ->find($value->id);
            }

            $is_new_ajuste = false;
            if ($ajuste_entity == null) {
                $ajuste_entity = new ProjectPriceAdjustment();
                $is_new_ajuste = true;
            }

            $ajuste_entity->setPercent($value->percent);

            if ($value->day != '') {
                $day = \DateTime::createFromFormat('m/d/Y', $value->day);
                $ajuste_entity->setDay($day);
            }


            if ($is_new_ajuste) {
                $ajuste_entity->setProject($entity);

                $em->persist($ajuste_entity);
            }
        }
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

            // si no encontro buscar en projects
            if (empty($projects)) {
                $projects = $this->getDoctrine()->getRepository(Project::class)
                    ->ListarProjects($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0,
                        $company_id, '', $status, $fecha_inicial, $fecha_fin);
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
                "subcontract" => $value->getSubcontract(),
                "name" => $value->getName(),
                "description" => $value->getDescription(),
                "company" => $value->getCompany()->getName(),
                "county" => $value->getCountyObj() ? $value->getCountyObj()->getDescription() : "",
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

        $acciones = '<a href="javascript:;" class="view m-portlet__nav-link btn m-btn m-btn--hover-info m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';

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