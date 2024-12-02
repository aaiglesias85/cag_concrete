<?php

namespace App\Utils\Admin;

use App\Entity\DataTrackingItem;
use App\Entity\Equation;
use App\Entity\InvoiceItem;
use App\Entity\Item;
use App\Entity\DataTracking;
use App\Entity\ProjectItem;
use App\Entity\Unit;
use App\Utils\Base;

class ItemService extends Base
{

    /**
     * CargarDatosItem: Carga los datos de un item
     *
     * @param int $item_id Id
     *
     * @author Marcel
     */
    public function CargarDatosItem($item_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Item::class)
            ->find($item_id);
        /** @var Item $entity */
        if ($entity != null) {

            $arreglo_resultado['descripcion'] = $entity->getDescription();
            // $arreglo_resultado['price'] = $entity->getPrice();
            $arreglo_resultado['status'] = $entity->getStatus();
            $arreglo_resultado['unit_id'] = $entity->getUnit()->getUnitId();
            $arreglo_resultado['yield_calculation'] = $entity->getYieldCalculation();
            $arreglo_resultado['equation_id'] = $entity->getEquation() != null ? $entity->getEquation()->getEquationId() : '';

            $resultado['success'] = true;
            $resultado['item'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarItem: Elimina un rol en la BD
     * @param int $item_id Id
     * @author Marcel
     */
    public function EliminarItem($item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Item::class)
            ->find($item_id);
        /**@var Item $entity */
        if ($entity != null) {

            // verificar si se puede eliminar
            /*$se_puede_eliminar = $this->SePuedeEliminarItem($item_id);
            if ($se_puede_eliminar != '') {
                $resultado['success'] = false;
                $resultado['error'] = $se_puede_eliminar;
                return $resultado;
            }*/

            // eliminar informacion relacionada
            $this->EliminarInformacionDeItem($item_id);

            $item_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Item";
            $log_descripcion = "The item is deleted: $item_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarInformacionDeItem
     * @param $item_id
     * @return void
     */
    private function EliminarInformacionDeItem($item_id)
    {
        $em = $this->getDoctrine()->getManager();

        // project items
        $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarProjectsDeItem($item_id);
        foreach ($project_items as $project_item) {
            $project_item_id = $project_item->getId();

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

            $em->remove($project_item);
        }
    }

    /**
     * SePuedeEliminarItem
     * @param $item_id
     * @return string
     */
    private function SePuedeEliminarItem($item_id)
    {
        $texto_error = '';

        //projects
        $projects = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarProjectsDeItem($item_id);
        if (!empty($projects)) {
            $texto_error = "The item could not be deleted, because it has associated projects";
        }

        return $texto_error;

    }

    /**
     * EliminarItems: Elimina los items seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarItems($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $item_id) {
                if ($item_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Item::class)
                        ->find($item_id);
                    /**@var Item $entity */
                    if ($entity != null) {

                        // eliminar informacion relacionada
                        $this->EliminarInformacionDeItem($item_id);

                        $item_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Item";
                        $log_descripcion = "The item is deleted: $item_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);


                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The items could not be deleted, because they are associated with a projects or invoices";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected items because they are associated with a projects or invoices";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarItem: Actuializa los datos del rol en la BD
     * @param int $item_id Id
     * @author Marcel
     */
    public function ActualizarItem($item_id, $unit_id, $description, $status, $yield_calculation, $equation_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Item::class)
            ->find($item_id);
        /** @var Item $entity */
        if ($entity != null) {
            //Verificar description
            $item = $this->getDoctrine()->getRepository(Item::class)
                ->findOneBy(['description' => $description]);
            if ($item != null && $entity->getItemId() != $item->getItemId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The item name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            // $entity->setPrice($price);
            $entity->setStatus($status);
            $entity->setYieldCalculation($yield_calculation);

            if ($unit_id != '') {
                $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
                $entity->setUnit($unit);
            }

            $entity->setEquation(NULL);
            if ($equation_id != '') {
                $equation = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
                $entity->setEquation($equation);
            }

            $entity->setUpdatedAt(new \DateTime());

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Item";
            $log_descripcion = "The item is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarItem: Guarda los datos de item en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarItem($unit_id, $description, $status, $yield_calculation, $equation_id)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar description
        $item = $this->getDoctrine()->getRepository(Item::class)
            ->findOneBy(['description' => $description]);
        if ($item != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The item name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Item();

        $entity->setDescription($description);
        // $entity->setPrice($price);
        $entity->setStatus($status);
        $entity->setYieldCalculation($yield_calculation);

        if ($unit_id != '') {
            $unit = $this->getDoctrine()->getRepository(Unit::class)->find($unit_id);
            $entity->setUnit($unit);
        }

        if ($equation_id != '') {
            $equation = $this->getDoctrine()->getRepository(Equation::class)->find($equation_id);
            $entity->setEquation($equation);
        }

        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Item";
        $log_descripcion = "The item is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarItems: Listar los items
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Item::class)
            ->ListarItems($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $item_id = $value->getItemId();

            $acciones = $this->ListarAcciones($item_id);

            $yield_calculation = $this->DevolverYieldCalculationDeItem($value);

            $arreglo_resultado[$cont] = array(
                "id" => $item_id,
                "description" => $value->getDescription(),
                // "price" => number_format($value->getPrice(), 2, '.', ','),
                "status" => $value->getStatus() ? 1 : 0,
                "unit" => $value->getUnit()->getDescription(),
                "yieldCalculation" => $yield_calculation,
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * DevolverYieldCalculationDeItem
     * @param Item $item_entity
     * @return string
     */
    private function DevolverYieldCalculationDeItem($item_entity)
    {
        $yield_calculation = $item_entity->getYieldCalculation();

        $yield_calculation_name = $this->BuscarYieldCalculation($yield_calculation);

        // para la ecuacion devuelvo la ecuacion asociada
        if ($yield_calculation == 'equation' && $item_entity->getEquation() != null) {
            $yield_calculation_name = $item_entity->getEquation()->getEquation();
        }

        return $yield_calculation_name;
    }

    /**
     * TotalItems: Total de items
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalItems($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Item::class)
            ->TotalItems($sSearch);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 6);

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