<?php

namespace App\Utils\Admin;

use App\Entity\Item;
use App\Entity\Material;
use App\Entity\Unit;
use App\Utils\Base;

class UnitService extends Base
{

    /**
     * CargarDatosUnit: Carga los datos de un unit
     *
     * @param int $unit_id Id
     *
     * @author Marcel
     */
    public function CargarDatosUnit($unit_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(Unit::class)
            ->find($unit_id);
        /** @var Unit $entity */
        if ($entity != null) {

            $arreglo_resultado['descripcion'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['unit'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarUnit: Elimina un rol en la BD
     * @param int $unit_id Id
     * @author Marcel
     */
    public function EliminarUnit($unit_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Unit::class)
            ->find($unit_id);
        /**@var Unit $entity */
        if ($entity != null) {

            // items
            $items = $this->getDoctrine()->getRepository(Item::class)
                ->ListarItemsDeUnit($unit_id);
            if (count($items) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The unit could not be deleted, because it is related to a item";
                return $resultado;
            }

            // materiales
            $materiales = $this->getDoctrine()->getRepository(Material::class)
                ->ListarMaterialsDeUnit($unit_id);
            if (count($materiales) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The unit could not be deleted, because it is related to a material";
                return $resultado;
            }

            $unit_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Unit";
            $log_descripcion = "The unit is deleted: $unit_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarUnits: Elimina los units seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarUnits($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $unit_id) {
                if ($unit_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(Unit::class)
                        ->find($unit_id);
                    /**@var Unit $entity */
                    if ($entity != null) {

                        $items = $this->getDoctrine()->getRepository(Item::class)
                            ->ListarItemsDeUnit($unit_id);

                        $materiales = $this->getDoctrine()->getRepository(Material::class)
                            ->ListarMaterialsDeUnit($unit_id);

                        if (count($items) == 0 && count($materiales) == 0) {

                            $unit_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Unit";
                            $log_descripcion = "The unit is deleted: $unit_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The units could not be deleted, because they are associated with a item";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected units because they are associated with a item";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarUnit: Actuializa los datos del rol en la BD
     * @param int $unit_id Id
     * @author Marcel
     */
    public function ActualizarUnit($unit_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(Unit::class)
            ->find($unit_id);
        /** @var Unit $entity */
        if ($entity != null) {
            //Verificar description
            $unit = $this->getDoctrine()->getRepository(Unit::class)
                ->findOneBy(['description' => $description]);
            if ($unit != null && $entity->getUnitId() != $unit->getUnitId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The unit name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Unit";
            $log_descripcion = "The unit is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['unit_id'] = $unit_id;

            return $resultado;
        }
    }

    /**
     * SalvarUnit: Guarda los datos de unit en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarUnit($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar description
        $unit = $this->getDoctrine()->getRepository(Unit::class)
            ->findOneBy(['description' => $description]);
        if ($unit != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The unit name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new Unit();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Unit";
        $log_descripcion = "The unit is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['unit_id'] = $entity->getUnitId();

        return $resultado;
    }

    /**
     * ListarUnits: Listar los units
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarUnits($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(Unit::class)
            ->ListarUnits($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $unit_id = $value->getUnitId();

            $acciones = $this->ListarAcciones($unit_id);

            $arreglo_resultado[$cont] = array(
                "id" => $unit_id,
                "description" => $value->getDescription(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalUnits: Total de units
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalUnits($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(Unit::class)
            ->TotalUnits($sSearch);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 5);

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