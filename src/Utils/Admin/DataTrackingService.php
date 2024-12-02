<?php

namespace App\Utils\Admin;

use App\Entity\DataTracking;
use App\Entity\DataTrackingConcVendor;
use App\Entity\DataTrackingItem;
use App\Entity\DataTrackingLabor;
use App\Entity\DataTrackingMaterial;
use App\Entity\Employee;
use App\Entity\Inspector;
use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Project;
use App\Entity\ProjectItem;
use App\Utils\Base;

class DataTrackingService extends Base
{

    /**
     * EliminarConcVendorDataTracking: Elimina un conc vendor en la BD
     * @param int $data_tracking_conc_vendor_id Id
     * @author Marcel
     */
    public function EliminarConcVendorDataTracking($data_tracking_conc_vendor_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->find($data_tracking_conc_vendor_id);
        /**@var DataTrackingConcVendor $entity */
        if ($entity != null) {


            $project_name = $entity->getDataTracking()->getProject()->getProjectNumber() . " - " . $entity->getDataTracking()->getProject()->getName();
            $date = $entity->getDataTracking()->getDate()->format('m/d/Y');

            $conc_vendor = $entity->getConcVendor();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Data Tracking";
            $log_descripcion = "The conc vendor of the data tracking is deleted, Conc Vendor: $conc_vendor, Project: $project_name, Date: $date";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarMaterialDataTracking: Elimina un material en la BD
     * @param int $data_tracking_material_id Id
     * @author Marcel
     */
    public function EliminarMaterialDataTracking($data_tracking_material_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
            ->find($data_tracking_material_id);
        /**@var DataTrackingMaterial $entity */
        if ($entity != null) {


            $project_name = $entity->getDataTracking()->getProject()->getProjectNumber() . " - " . $entity->getDataTracking()->getProject()->getName();
            $date = $entity->getDataTracking()->getDate()->format('m/d/Y');

            $material_name = $entity->getMaterial()->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Data Tracking";
            $log_descripcion = "The material of the data tracking is deleted, Material: $material_name, Project: $project_name, Date: $date";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarLaborDataTracking: Elimina un employee en la BD
     * @param int $data_tracking_labor_id Id
     * @author Marcel
     */
    public function EliminarLaborDataTracking($data_tracking_labor_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->find($data_tracking_labor_id);
        /**@var DataTrackingLabor $entity */
        if ($entity != null) {


            $project_name = $entity->getDataTracking()->getProject()->getProjectNumber() . " - " . $entity->getDataTracking()->getProject()->getName();
            $date = $entity->getDataTracking()->getDate()->format('m/d/Y');

            $employee_name = $entity->getEmployee()->getName();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Data Tracking";
            $log_descripcion = "The employee of the data tracking is deleted, Employee: $employee_name, Project: $project_name, Date: $date";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarItemDataTracking: Elimina un item details en la BD
     * @param int $data_tracking_item_id Id
     * @author Marcel
     */
    public function EliminarItemDataTracking($data_tracking_item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->find($data_tracking_item_id);
        /**@var DataTrackingItem $entity */
        if ($entity != null) {


            $project_name = $entity->getProjectItem()->getProject()->getProjectNumber() . " - " . $entity->getProjectItem()->getProject()->getName();
            $date = $entity->getDataTracking()->getDate()->format('m/d/Y');

            $item_name = $entity->getProjectItem()->getItem()->getDescription();

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Data Tracking";
            $log_descripcion = "The item of the data tracking is deleted, Item: $item_name, Project: $project_name, Date: $date";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * CargarDatosDataTracking: Carga los datos de un item project
     *
     * @param int $data_tracking_id Id
     * @param ProjectService $projectService
     *
     * @author Marcel
     */
    public function CargarDatosDataTracking($data_tracking_id, $projectService)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(DataTracking::class)
            ->find($data_tracking_id);
        /** @var DataTracking $entity */
        if ($entity != null) {

            $project_id = $entity->getProject()->getProjectId();
            $arreglo_resultado['project_id'] = $project_id;
            $arreglo_resultado['project_number'] = $entity->getProject()->getProjectNumber();
            $arreglo_resultado['project_name'] = $entity->getProject()->getName();

            $arreglo_resultado['date'] = $entity->getDate()->format('m/d/Y');
            $arreglo_resultado['inspector_id'] = $entity->getInspector() != null ? $entity->getInspector()->getInspectorId() : '';
            $arreglo_resultado['station_number'] = $entity->getStationNumber();
            $arreglo_resultado['measured_by'] = $entity->getMeasuredBy();
            $arreglo_resultado['conc_vendor'] = $entity->getConcVendor();

            $total_conc_used = $entity->getTotalConcUsed();
            $arreglo_resultado['total_conc_used'] = $total_conc_used;

            $conc_price = $entity->getConcPrice();
            $arreglo_resultado['conc_price'] = $conc_price;

            $arreglo_resultado['crew_lead'] = $entity->getCrewLead();
            $arreglo_resultado['notes'] = $entity->getNotes();
            $arreglo_resultado['other_materials'] = $entity->getOtherMaterials();

            $total_people = $entity->getTotalPeople();
            $arreglo_resultado['total_people'] = $total_people;

            $overhead_price = $entity->getOverheadPrice();
            $arreglo_resultado['overhead_price'] = $overhead_price;

            $arreglo_resultado['total_stamps'] = $entity->getTotalStamps();

            // conc vendors
            $conc_vendors = $this->ListarConcVendorsDeDataTracking($data_tracking_id);
            $arreglo_resultado['conc_vendors'] = $conc_vendors;

            // items
            $items = $this->ListarItemsDeDataTracking($data_tracking_id);
            $arreglo_resultado['items'] = $items;

            // project items
            $arreglo_resultado['project_items'] = $projectService->ListarItemsDeProject($project_id);

            // labor
            $labor = $this->ListarLaborDeDataTracking($data_tracking_id);
            $arreglo_resultado['labor'] = $labor;

            // materials
            $materials = $this->ListarMaterialsDeDataTracking($data_tracking_id);
            $arreglo_resultado['materials'] = $materials;


            // totales
            $lost_concrete = $this->CalcularLostConcrete($entity);
            $arreglo_resultado['lost_concrete'] = $lost_concrete;

            $total_concrete_yiel = $this->CalcularTotalConcreteYiel($data_tracking_id);
            $arreglo_resultado['total_concrete_yiel'] = $total_concrete_yiel;

            $total_quantity_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity($data_tracking_id);
            $arreglo_resultado['total_quantity_today'] = $total_quantity_today;

            $total_daily_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalDaily($data_tracking_id);
            $arreglo_resultado['total_daily_today'] = $total_daily_today;


            $total_concrete = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcPrice($data_tracking_id);
            $arreglo_resultado['total_concrete'] = $total_concrete;

            $total_labor_price = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                ->TotalLabor($data_tracking_id);
            $arreglo_resultado['total_labor_price'] = $total_labor_price;

            $profit = $total_daily_today - ($total_concrete + $total_labor_price);
            $arreglo_resultado['profit'] = $profit;

            $resultado['success'] = true;
            $resultado['data_tracking'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarConcVendorsDeDataTracking
     * @param $data_tracking_id
     * @return array
     */
    public function ListarConcVendorsDeDataTracking($data_tracking_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->ListarConcVendor($data_tracking_id);
        foreach ($lista as $key => $value) {

            $total_conc_used = $value->getTotalConcUsed();
            $conc_price = $value->getConcPrice();
            $total = $total_conc_used * $conc_price;

            $items[] = [
                'data_tracking_conc_vendor_id' => $value->getId(),
                "conc_vendor" => $value->getConcVendor(),
                "total_conc_used" => $total_conc_used,
                "conc_price" => $conc_price,
                "total" => $total,
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * ListarMaterialsDeDataTracking
     * @param $data_tracking_id
     * @return array
     */
    public function ListarMaterialsDeDataTracking($data_tracking_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
            ->ListarMaterials($data_tracking_id);
        foreach ($lista as $key => $value) {

            $quantity = $value->getQuantity();
            $price = $value->getPrice();
            $total = $quantity * $price;

            $items[] = [
                'data_tracking_material_id' => $value->getId(),
                "material_id" => $value->getMaterial()->getMaterialId(),
                "material" => $value->getMaterial()->getName(),
                "unit" => $value->getMaterial()->getUnit()->getDescription(),
                "quantity" => $quantity,
                "price" => $price,
                "total" => $total,
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * ListarLaborDeDataTracking
     * @param $data_tracking_id
     * @return array
     */
    public function ListarLaborDeDataTracking($data_tracking_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarLabor($data_tracking_id);
        foreach ($lista as $key => $value) {

            $hours = $value->getHours();
            $hourly_rate = $value->getHourlyRate();
            $total = $hours * $hourly_rate;

            $items[] = [
                'data_tracking_labor_id' => $value->getId(),
                "employee_id" => $value->getEmployee()->getEmployeeId(),
                "employee" => $value->getEmployee()->getName(),
                "hours" => $hours,
                "hourly_rate" => $hourly_rate,
                "total" => $total,
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * ListarItemsDeDataTracking
     * @param $data_tracking_id
     * @return array
     */
    public function ListarItemsDeDataTracking($data_tracking_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarItems($data_tracking_id);
        foreach ($lista as $key => $value) {

            $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value->getProjectItem());

            $quantity = $value->getQuantity();
            $price = $value->getPrice();
            $total = $quantity * $price;

            $items[] = [
                'data_tracking_item_id' => $value->getId(),
                "item_id" => $value->getProjectItem()->getId(),
                "item" => $value->getProjectItem()->getItem()->getDescription(),
                "unit" => $value->getProjectItem()->getItem()->getUnit()->getDescription(),
                "quantity" => $quantity,
                "price" => $price,
                "total" => $total,
                "yield_calculation" => $value->getProjectItem()->getYieldCalculation(),
                "yield_calculation_name" => $yield_calculation_name,
                "equation_id" => $value->getProjectItem()->getEquation() != null ? $value->getProjectItem()->getEquation()->getEquationId() : '',
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * EliminarDataTracking: Elimina un item details en la BD
     * @param int $data_tracking_id Id
     * @author Marcel
     */
    public function EliminarDataTracking($data_tracking_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(DataTracking::class)
            ->find($data_tracking_id);
        /**@var DataTracking $entity */
        if ($entity != null) {

            // eliminar informacion relacionada
            $this->EliminarInformacionRelacionada($data_tracking_id);


            $project_name = $entity->getProject()->getProjectNumber() . " - " . $entity->getProject()->getName();
            $date = $entity->getDate()->format('m/d/Y');

            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Data Tracking";
            $log_descripcion = "The data tracking is deleted, Project: $project_name, Date: $date";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarDataTrackings: Elimina los data trackings
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarDataTrackings($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $data_tracking_id) {
                if ($data_tracking_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(DataTracking::class)
                        ->find($data_tracking_id);
                    /**@var DataTracking $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionRelacionada($data_tracking_id);

                        $project_name = $entity->getProject()->getProjectNumber() . " - " . $entity->getProject()->getName();
                        $date = $entity->getDate()->format('m/d/Y');

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Item Project";
                        $log_descripcion = "The data tracking is deleted, Project: $project_name, Date: $date";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The projects could not be deleted, because they are associated with a project";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected projects because they are associated with a project";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * EliminarInformacionRelacionada
     * @param $data_tracking_id
     * @return void
     */
    private function EliminarInformacionRelacionada($data_tracking_id)
    {
        $em = $this->getDoctrine()->getManager();

        // conc vendors
        $conc_vendors = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
            ->ListarConcVendor($data_tracking_id);
        foreach ($conc_vendors as $conc_vendor) {
            $em->remove($conc_vendor);
        }

        // items
        $items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarItems($data_tracking_id);
        foreach ($items as $item) {
            $em->remove($item);
        }

        // labor
        $data_tracking_labors = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
            ->ListarLabor($data_tracking_id);
        foreach ($data_tracking_labors as $data_tracking_labor) {
            $em->remove($data_tracking_labor);
        }

        // materials
        $data_tracking_materials = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
            ->ListarMaterials($data_tracking_id);
        foreach ($data_tracking_materials as $data_tracking_material) {
            $em->remove($data_tracking_material);
        }
    }

    /**
     * SalvarDataTracking
     * @param $data_tracking_id
     * @param $project_item_id
     * @return array
     */
    public function SalvarDataTracking($data_tracking_id, $project_id, $date, $inspector_id,
                                       $station_number, $measured_by, $conc_vendor, $conc_price, $crew_lead, $notes, $other_materials,
                                       $total_conc_used, $total_stamps, $total_people, $overhead_price, $items, $labor, $materials, $conc_vendors)
    {

        $em = $this->getDoctrine()->getManager();

        // validar que no exista el datatracking
        $existe_data_tracking = $this->ValidarSiExisteDataTracking($data_tracking_id, $project_id, $date);
        if ($existe_data_tracking) {
            $resultado['success'] = false;
            $resultado['error'] = "A record already exists for the selected project and date";

            return $resultado;
        }


        // validar project
        $project_entity = null;
        if ($data_tracking_id == '') {
            $project_entity = $this->getDoctrine()->getRepository(Project::class)
                ->find($project_id);
            if ($project_entity == null) {
                $resultado['success'] = false;
                $resultado['error'] = "The project not exist.";

                return $resultado;
            }
        }

        $entity = null;
        $is_new = false;

        if (is_numeric($data_tracking_id)) {
            $entity = $this->getDoctrine()->getRepository(DataTracking::class)
                ->find($data_tracking_id);
        }

        if ($entity == null) {
            $entity = new DataTracking();
            $is_new = true;
        }

        if ($date != '') {
            $entity->setDate(\DateTime::createFromFormat('m/d/Y', $date));
        }

        if ($inspector_id != '') {
            $inspector_entity = $this->getDoctrine()->getRepository(Inspector::class)
                ->find($inspector_id);
            $entity->setInspector($inspector_entity);
        }

        $entity->setStationNumber($station_number);
        $entity->setMeasuredBy($measured_by);
        $entity->setConcVendor($conc_vendor);
        $entity->setConcPrice($conc_price);
        $entity->setCrewLead($crew_lead);
        $entity->setNotes($notes);
        $entity->setOtherMaterials($other_materials);
        $entity->setTotalConcUsed($total_conc_used);
        $entity->setTotalStamps($total_stamps);
        $entity->setTotalPeople($total_people);
        $entity->setOverheadPrice($overhead_price);

        if ($is_new) {

            $entity->setProject($project_entity);

            $entity->setCreatedAt(new \DateTime());

            $em->persist($entity);

            $log_operacion = "Add";
            $project_desc = $project_entity->getProjectNumber() . ' - ' . $project_entity->getName();
            $log_descripcion = "The data tracking is add, Project: $project_desc, Date: $date";

        } else {

            $entity->setUpdatedAt(new \DateTime());

            $project_entity = $entity->getProject();

            $log_operacion = "Update";
            $project_desc = $project_entity->getProjectNumber() . ' - ' . $project_entity->getName();
            $log_descripcion = "The data tracking is modified, Project: $project_desc, Date: $date";
        }

        // conc vendors
        $this->SalvarConcVendors($entity, $conc_vendors);
        // items
        $this->SalvarItems($entity, $items);
        // labor
        $this->SalvarLabor($entity, $labor);
        // materials
        $this->SalvarMaterials($entity, $materials);

        $em->flush();

        //Salvar log
        $log_categoria = "Data Tracking";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;

    }

    /**
     * SalvarConcVendors
     * @param array $conc_vendors
     * @param DataTracking $entity
     * @return array
     */
    public function SalvarConcVendors($entity, $conc_vendors)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($conc_vendors as $value) {

            $data_tracking_conc_vendor_entity = null;

            if (is_numeric($value->data_tracking_conc_vendor_id)) {
                $data_tracking_conc_vendor_entity = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                    ->find($value->data_tracking_conc_vendor_id);
            }

            $is_new_data_tracking_conc_vendor = false;
            if ($data_tracking_conc_vendor_entity == null) {
                $data_tracking_conc_vendor_entity = new DataTrackingConcVendor();
                $is_new_data_tracking_conc_vendor = true;
            }

            $data_tracking_conc_vendor_entity->setConcVendor($value->conc_vendor);
            $data_tracking_conc_vendor_entity->setTotalConcUsed($value->total_conc_used);
            $data_tracking_conc_vendor_entity->setConcPrice($value->conc_price);

            if ($is_new_data_tracking_conc_vendor) {
                $data_tracking_conc_vendor_entity->setDataTracking($entity);

                $em->persist($data_tracking_conc_vendor_entity);
            }
        }
    }

    /**
     * SalvarMaterials
     * @param array $materials
     * @param DataTracking $entity
     * @return array
     */
    public function SalvarMaterials($entity, $materials)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($materials as $value) {

            $data_tracking_material_entity = null;

            if (is_numeric($value->data_tracking_material_id)) {
                $data_tracking_material_entity = $this->getDoctrine()->getRepository(DataTrackingMaterial::class)
                    ->find($value->data_tracking_material_id);
            }

            $is_new_data_tracking_material = false;
            if ($data_tracking_material_entity == null) {
                $data_tracking_material_entity = new DataTrackingMaterial();
                $is_new_data_tracking_material = true;
            }

            $data_tracking_material_entity->setPrice($value->price);
            $data_tracking_material_entity->setQuantity($value->quantity);

            if ($value->material_id != '') {
                $material_entity = $this->getDoctrine()->getRepository(Material::class)
                    ->find($value->material_id);
                $data_tracking_material_entity->setMaterial($material_entity);
            }

            if ($is_new_data_tracking_material) {
                $data_tracking_material_entity->setDataTracking($entity);

                $em->persist($data_tracking_material_entity);
            }
        }
    }

    /**
     * SalvarLabor
     * @param array $labor
     * @param DataTracking $entity
     * @return array
     */
    public function SalvarLabor($entity, $labor)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($labor as $value) {

            $data_tracking_labor_entity = null;

            if (is_numeric($value->data_tracking_labor_id)) {
                $data_tracking_labor_entity = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                    ->find($value->data_tracking_labor_id);
            }

            $is_new_data_tracking_labor = false;
            if ($data_tracking_labor_entity == null) {
                $data_tracking_labor_entity = new DataTrackingLabor();
                $is_new_data_tracking_labor = true;
            }

            $data_tracking_labor_entity->setHourlyRate($value->hourly_rate);
            $data_tracking_labor_entity->setHours($value->hours);

            if ($value->employee_id != '') {
                $employee_entity = $this->getDoctrine()->getRepository(Employee::class)
                    ->find($value->employee_id);
                $data_tracking_labor_entity->setEmployee($employee_entity);
            }

            if ($is_new_data_tracking_labor) {
                $data_tracking_labor_entity->setDataTracking($entity);

                $em->persist($data_tracking_labor_entity);
            }
        }
    }

    /**
     * ValidarSiExisteDataTracking
     * @param $data_tracking_id
     * @param $project_id
     * @return boolean
     */
    public function ValidarSiExisteDataTracking($data_tracking_id, $project_id, $date)
    {
        $existe = false;

        if ($project_id == '' && $data_tracking_id != '') {
            $entity = $this->getDoctrine()->getRepository(DataTracking::class)
                ->find($data_tracking_id);
            /** @var DataTracking $entity */
            $project_id = $entity->getProject()->getProjectId();
        }

        $data_trackings = $this->getDoctrine()->getRepository(DataTracking::class)
            ->ListarDataTracking($project_id, $date, $date);

        if ($data_tracking_id == '' && !empty($data_trackings)) {
            $existe = true;
        } else if (!empty($data_trackings) && $data_trackings[0]->getId() != $data_tracking_id) {
            $existe = true;
        }

        return $existe;
    }

    /**
     * SalvarItems
     * @param array $items
     * @param DataTracking $entity
     * @return array
     */
    public function SalvarItems($entity, $items)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($items as $value) {

            $data_tracking_item_entity = null;

            if (is_numeric($value->data_tracking_item_id)) {
                $data_tracking_item_entity = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                    ->find($value->data_tracking_item_id);
            }

            $is_new_data_tracking_item = false;
            if ($data_tracking_item_entity == null) {
                $data_tracking_item_entity = new DataTrackingItem();
                $is_new_data_tracking_item = true;
            }

            $data_tracking_item_entity->setPrice($value->price);
            $data_tracking_item_entity->setQuantity($value->quantity);

            if ($value->item_id != '') {
                $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                    ->find($value->item_id);
                $data_tracking_item_entity->setProjectItem($project_item_entity);
            }

            if ($is_new_data_tracking_item) {
                $data_tracking_item_entity->setDataTracking($entity);

                $em->persist($data_tracking_item_entity);
            }
        }
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
    public function ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin)
    {
        $arreglo_resultado = array();

        $lista = $this->getDoctrine()->getRepository(DataTracking::class)
            ->ListarDataTrackings($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $project_id, $fecha_inicial, $fecha_fin);

        foreach ($lista as $value) {
            $data_tracking_id = $value->getId();

            $acciones = $this->ListarAcciones($data_tracking_id);

            $lost_concrete = $this->CalcularLostConcrete($value);
            // totales
            $total_concrete_yiel = $this->CalcularTotalConcreteYiel($data_tracking_id);

            $total_quantity_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalQuantity($data_tracking_id);

            $total_daily_today = $this->getDoctrine()->getRepository(DataTrackingItem::class)
                ->TotalDaily($data_tracking_id);

            // conc vendor
            $total_conc_used = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcUsed($data_tracking_id);

            $total_concrete = $this->getDoctrine()->getRepository(DataTrackingConcVendor::class)
                ->TotalConcPrice($data_tracking_id);


            $total_labor = $this->getDoctrine()->getRepository(DataTrackingLabor::class)
                ->TotalLabor($data_tracking_id);

            $profit = $total_daily_today - ($total_concrete + $total_labor);

            $total_people = $value->getTotalPeople();
            $overhead_price = $value->getOverheadPrice();
            $total_overhead = $total_people * $overhead_price;

            $arreglo_resultado[] = [
                "id" => $data_tracking_id,
                'project' => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getName(),
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
                "totalStamps" => $value->getTotalStamps(),
                "otherMaterials" => $value->getOtherMaterials(),
                // overhead
                "totalPeople" => $total_people,
                "overheadPrice" => $overhead_price,
                "totalOverhead" => $total_overhead,
                // totales
                "total_concrete_yiel" => $total_concrete_yiel,
                'total_quantity_today' => $total_quantity_today != null ? $total_quantity_today : 0,
                'total_daily_today' => $total_daily_today,
                'total_concrete' => $total_concrete,
                'profit' => $profit,
                'acciones' => $acciones
            ];
        }

        return $arreglo_resultado;
    }

    /**
     * TotalDataTrackings: Total de items
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin)
    {
        $total = $this->getDoctrine()->getRepository(DataTracking::class)
            ->TotalDataTrackings($sSearch, $project_id, $fecha_inicial, $fecha_fin);

        return $total;
    }

    /**
     * CalcularTotalConcreteYiel
     * @param $data_tracking_id
     * @return float
     */
    private function CalcularTotalConcreteYiel($data_tracking_id)
    {
        $total_conc_yiel = 0;

        $data_tracking_items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarItems($data_tracking_id);
        foreach ($data_tracking_items as $data_tracking_item) {

            // aplicar el yield
            $quantity_yield = 0;
            if ($data_tracking_item->getProjectItem()->getYieldCalculation() == "equation" && $data_tracking_item->getProjectItem()->getEquation() != null) {
                $quantity = $data_tracking_item->getQuantity();
                $quantity_yield = $this->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
            }

            $total_conc_yiel += $quantity_yield;

        }

        return $total_conc_yiel;
    }

    /**
     * CalcularLostConcrete
     * @param DataTracking $value
     * @return float
     */
    private function CalcularLostConcrete($value)
    {
        $total_conc_item = 0;
        $total_conc_used = $value->getTotalConcUsed();

        $data_tracking_id = $value->getId();

        $data_tracking_items = $this->getDoctrine()->getRepository(DataTrackingItem::class)
            ->ListarItems($data_tracking_id);
        foreach ($data_tracking_items as $data_tracking_item) {

            // aplicar el yield
            $quantity = $data_tracking_item->getQuantity();
            $quantity_yield = $quantity;
            if ($data_tracking_item->getProjectItem()->getYieldCalculation() == "equation" && $data_tracking_item->getProjectItem()->getEquation() != null) {
                $quantity_yield = $this->evaluateExpression($data_tracking_item->getProjectItem()->getEquation()->getEquation(), $quantity);
            }

            $total_conc_item += $quantity_yield;

        }

        return round($total_conc_used - $total_conc_item, 2);
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 10);

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