<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Equation;
use App\Entity\ProjectItem;
use App\Utils\Base;

class EquationService extends Base
{

    /**
     * SalvarPayItems: Guarda los datos en la BD
     * @param $pay_items
     * @author Marcel
     */
    public function SalvarPayItems($pay_items)
    {
        $em = $this->getDoctrine()->getManager();

        foreach ($pay_items as $pay_item) {
            $project_item_entity = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->find($pay_item->project_item_id);

            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)->find($pay_item->equation_id);

            if($project_item_entity != null && $equation_entity != null){
                $project_item_entity->setEquation($equation_entity);
            }
        }

        $em->flush();

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarPayItems: Lista los pay items
     * @param int $ids Ids
     * @author Marcel
     */
    public function ListarPayItems($ids)
    {
        $items = [];

        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $project_items = $this->ListarItemsDeProject($id);
            $items = array_merge($items, $project_items);
        }

        return $items;
    }

    /**
     * CargarDatosEquation: Carga los datos de un equation
     *
     * @param int $equation_id Id
     *
     * @author Marcel
     */
    public function CargarDatosEquation($equation_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Equation::class)
            ->find($equation_id);
        /** @var Equation $entity */
        if ($entity != null) {

            $arreglo_resultado['descripcion'] = $entity->getDescription();
            $arreglo_resultado['equation'] = $entity->getEquation();
            $arreglo_resultado['status'] = $entity->getStatus();

            // items
            $items = $this->ListarItemsDeProject($equation_id);
            $arreglo_resultado['items'] = $items;

            $resultado['success'] = true;
            $resultado['equation'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * ListarItemsDeProject
     * @param $equation_id
     * @return array
     */
    public function ListarItemsDeProject($equation_id)
    {
        $items = [];

        $lista = $this->getDoctrine()->getRepository(ProjectItem::class)
            ->ListarProjectItemsDeEquation($equation_id);
        foreach ($lista as $key => $value) {

            $yield_calculation_name = $this->DevolverYieldCalculationDeItemProject($value);

            $quantity = $value->getQuantity();
            $price = $value->getPrice();
            $total = $quantity * $price;

            $items[] = [
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
                "project_id" => $value->getProject()->getProjectId(),
                "project" => $value->getProject()->getProjectNumber() . " - " . $value->getProject()->getName(),
                "posicion" => $key
            ];
        }

        return $items;
    }

    /**
     * EliminarEquation: Elimina un rol en la BD
     * @param int $equation_id Id
     * @author Marcel
     */
    public function EliminarEquation($equation_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Equation::class)
            ->find($equation_id);
        /**@var Equation $entity */
        if ($entity != null) {

            $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
                ->ListarProjectItemsDeEquation($equation_id);
            if (count($project_items) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The equation could not be deleted, because it is related to a item";
                $resultado['equation_ids_con_items'] = [$equation_id];
                return $resultado;
            }

            // items
            $items = $this->getDoctrine()->getRepository(Item::class)
                ->ListarItemsDeEquation($equation_id);
            foreach ($items as $item) {
                $item->setYieldCalculation(null);
                $item->setEquation(null);
            }

            $equation_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Equation";
            $log_descripcion = "The equation is deleted: $equation_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarEquations: Elimina los equations seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarEquations($ids)
    {
        $em = $this->getDoctrine()->getManager();

        $equation_ids_con_items = [];

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $equation_id) {
                if ($equation_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Equation::class)
                        ->find($equation_id);
                    /**@var Equation $entity */
                    if ($entity != null) {

                        $project_items = $this->getDoctrine()->getRepository(ProjectItem::class)
                            ->ListarProjectItemsDeEquation($equation_id);
                        if (count($project_items) == 0) {

                            // items
                            $items = $this->getDoctrine()->getRepository(Item::class)
                                ->ListarItemsDeEquation($equation_id);
                            foreach ($items as $item) {
                                $item->setYieldCalculation(null);
                                $item->setEquation(null);
                            }

                            $equation_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Equation";
                            $log_descripcion = "The equation is deleted: $equation_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        } else {
                            $equation_ids_con_items[] = $equation_id;
                        }
                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The equations could not be deleted, because they are associated with a item";
            $resultado['equation_ids_con_items'] = $equation_ids_con_items;
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected equations because they are associated with a item";
            $resultado['message'] = $mensaje;
            $resultado['equation_ids_con_items'] = $equation_ids_con_items;
        }

        return $resultado;
    }

    /**
     * ActualizarEquation: Actuializa los datos del rol en la BD
     * @param int $equation_id Id
     * @author Marcel
     */
    public function ActualizarEquation($equation_id, $description, $equation, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Equation::class)
            ->find($equation_id);
        /** @var Equation $entity */
        if ($entity != null) {
            //Verificar description
            $equation_entity = $this->getDoctrine()->getRepository(Equation::class)
                ->findOneBy(['description' => $description]);
            if ($equation_entity != null && $entity->getEquationId() != $equation_entity->getEquationId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The equation name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setEquation($equation);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Equation";
            $log_descripcion = "The equation is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['equation_id'] = $equation_id;

            return $resultado;
        }
    }

    /**
     * SalvarEquation: Guarda los datos de equation en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarEquation($description, $equation, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar description
        $equation_entity = $this->getDoctrine()->getRepository(Equation::class)
            ->findOneBy(['description' => $description]);
        if ($equation_entity != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The equation name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Equation();

        $entity->setDescription($description);
        $entity->setEquation($equation);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Equation";
        $log_descripcion = "The equation is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['equation_id'] = $entity->getEquationId();

        return $resultado;
    }

    /**
     * ListarEquations: Listar los equations
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarEquations($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Equation::class)
            ->ListarEquations($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $equation_id = $value->getEquationId();

            $acciones = $this->ListarAcciones($equation_id);

            $arreglo_resultado[$cont] = array(
                "id" => $equation_id,
                "description" => $value->getDescription(),
                "equation" => $value->getEquation(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalEquations: Total de equations
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalEquations($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Equation::class)
            ->TotalEquations($sSearch);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 13);

        $acciones = "";

        if (count($permiso) > 0) {
            if ($permiso[0]['editar']) {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="Edit record" data-id="' . $id . '"> <i class="la la-edit"></i> </a> ';
            } else {
                $acciones .= '<a href="javascript:;" class="edit m-portlet__nav-link btn m-btn m-btn--hover-success m-btn--icon m-btn--icon-only m-btn--pill" title="View record" data-id="' . $id . '"> <i class="la la-eye"></i> </a> ';
            }
            if ($permiso[0]['eliminar']) {
                $acciones .= ' <a href="javascript:;" class="delete m-portlet__nav-link btn m-btn m-btn--hover-danger m-btn--icon m-btn--icon-only m-btn--pill"
                 title="Delete record" data-id="' . $id . '"><i class="la la-trash"></i></a>';
            }
        }

        return $acciones;
    }
}