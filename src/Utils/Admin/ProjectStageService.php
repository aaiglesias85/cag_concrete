<?php

namespace App\Utils\Admin;

use App\Entity\ProjectStage;
use App\Utils\Base;

class ProjectStageService extends Base
{
    /**
     * CargarDatosStage: Carga los datos de un stage
     *
     * @param int $stage_id Id
     *
     * @author Marcel
     */
    public function CargarDatosStage($stage_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->find($stage_id);
        /** @var ProjectStage $entity */
        if ($entity != null) {

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['color'] = $entity->getColor();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['stage'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarStage: Elimina un stage en la BD
     * @param int $stage_id Id
     * @author Marcel
     */
    public function EliminarStage($stage_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->find($stage_id);
        /**@var ProjectStage $entity */
        if ($entity != null) {

            $stage_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Project Stage";
            $log_descripcion = "The project stage is deleted: $stage_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarStages: Elimina los stages seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarStages($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $stage_id) {
                if ($stage_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(ProjectStage::class)
                        ->find($stage_id);
                    /** @var ProjectStage $entity */
                    if ($entity != null) {

                        $stage_descripcion = $entity->getDescription();

                        $em->remove($entity);
                        $cant_eliminada++;

                        //Salvar log
                        $log_operacion = "Delete";
                        $log_categoria = "Project Stage";
                        $log_descripcion = "The project stage is deleted: $stage_descripcion";
                        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The project stages could not be deleted, because they are associated with a invoice";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected stages because they are associated with a invoice";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarStage: Actuializa los datos del stage en la BD
     * @param int $stage_id Id
     * @author Marcel
     */
    public function ActualizarStage($stage_id, $description, $color, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->find($stage_id);
        /** @var ProjectStage $entity */
        if ($entity != null) {

            //Verificar name
            $stage = $this->getDoctrine()->getRepository(ProjectStage::class)
                ->findOneBy(['description' => $description]);
            if ($stage != null && $entity->getStageId() != $stage->getStageId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The project stage name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setColor($color);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Project Stage";
            $log_descripcion = "The project stage is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['stage_id'] = $entity->getStageId();

            return $resultado;
        }
    }

    /**
     * SalvarStage: Guarda los datos de stage en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarStage($description, $color, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $stage = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->findOneBy(['description' => $description]);
        if ($stage != null ) {
            $resultado['success'] = false;
            $resultado['error'] = "The project stage name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new ProjectStage();

        $entity->setDescription($description);
        $entity->setColor($color);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Project Stage";
        $log_descripcion = "The project stage is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['stage_id'] = $entity->getStageId();

        return $resultado;
    }

    /**
     * ListarStages: Listar los stages
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarStages($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(ProjectStage::class)
            ->ListarStages($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $stage_id = $value->getStageId();

            $acciones = $this->ListarAcciones($stage_id);

            $arreglo_resultado[$cont] = array(
                "id" => $stage_id,
                "description" => $value->getDescription(),
                "color" => $value->getColor(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalStages: Total de stages
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalStages($sSearch)
    {
        return $this->getDoctrine()->getRepository(ProjectStage::class)
            ->TotalStages($sSearch);
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 24);

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