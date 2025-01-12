<?php

namespace App\Utils\Admin;

use App\Entity\DataTracking;
use App\Entity\OverheadPrice;

use App\Utils\Base;

class OverheadPriceService extends Base
{

    /**
     * CargarDatosOverhead: Carga los datos de un overhead
     *
     * @param int $overhead_id Id
     *
     * @author Marcel
     */
    public function CargarDatosOverhead($overhead_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->find($overhead_id);
        /** @var OverheadPrice $entity */
        if ($entity != null) {

            $arreglo_resultado['name'] = $entity->getName();
            $arreglo_resultado['price'] = $entity->getPrice();

            $resultado['success'] = true;
            $resultado['overhead'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarOverhead: Elimina un overhead en la BD
     * @param int $overhead_id Id
     * @author Marcel
     */
    public function EliminarOverhead($overhead_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->find($overhead_id);
        /**@var OverheadPrice $entity */
        if ($entity != null) {

            // data tracking
            $datatrackings = $this->getDoctrine()->getRepository(DataTracking::class)
                ->ListarDataTrackingsDeOverhead($overhead_id);
            if (count($datatrackings) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The overhead price could not be deleted, because it is related to a datatracking";
                return $resultado;
            }

            $overhead_descripcion = $entity->getName();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Overhead Price";
            $log_descripcion = "The overhead price is deleted: $overhead_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarOverheads: Elimina los overheads seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarOverheads($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $overhead_id) {
                if ($overhead_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
                        ->find($overhead_id);
                    /**@var OverheadPrice $entity */
                    if ($entity != null) {

                        // data tracking
                        $datatrackings = $this->getDoctrine()->getRepository(DataTracking::class)
                            ->ListarDataTrackingsDeOverhead($overhead_id);
                        if (count($datatrackings) == 0) {

                            $overhead_descripcion = $entity->getName();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Overhead Price";
                            $log_descripcion = "The overhead price is deleted: $overhead_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The overheads price could not be deleted, because they are associated with a datatracking";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected overheads price because they are associated with a datatracking";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarOverhead: Actuializa los datos del overhead en la BD
     * @param int $overhead_id Id
     * @author Marcel
     */
    public function ActualizarOverhead($overhead_id, $name, $price)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->find($overhead_id);
        /** @var OverheadPrice $entity */
        if ($entity != null) {
            
            //Verificar name
            $overhead = $this->getDoctrine()->getRepository(OverheadPrice::class)
                ->findOneBy(['name' => $name]);
            if ($overhead != null && $entity->getOverheadId() != $overhead->getOverheadId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The overhead name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setName($name);
            $entity->setPrice($price);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Overhead Price";
            $log_descripcion = "The overhead price is modified: $name";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;

            return $resultado;
        }
    }

    /**
     * SalvarOverhead: Guarda los datos de overhead en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarOverhead($name, $price)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar email
        $overhead = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->findOneBy(['name' => $name]);
        if ($overhead != null) {
            $resultado['success'] = false;
            $resultado['error'] = "The overhead name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new OverheadPrice();

        $entity->setName($name);
        $entity->setPrice($price);
        
        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Overhead Price";
        $log_descripcion = "The overhead price is added: $name";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;

        return $resultado;
    }

    /**
     * ListarOverheads: Listar los overheads
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarOverheads($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->ListarOverheads($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $overhead_id = $value->getOverheadId();

            $acciones = $this->ListarAcciones($overhead_id);

            $arreglo_resultado[$cont] = array(
                "id" => $overhead_id,
                "name" => $value->getName(),
                "price" => $value->getPrice(),
                "acciones" => $acciones
            );

            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalOverheads: Total de overheads
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalOverheads($sSearch)
    {
        $total = $this->getDoctrine()->getRepository(OverheadPrice::class)
            ->TotalOverheads($sSearch);

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
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 16);

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