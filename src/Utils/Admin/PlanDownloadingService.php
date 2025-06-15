<?php

namespace App\Utils\Admin;

use App\Entity\Estimate;
use App\Entity\PlanDownloading;
use App\Utils\Base;

class PlanDownloadingService extends Base
{
    /**
     * CargarDatosPlan: Carga los datos de un plan downloading
     *
     * @param int $plan_downloading_id Id
     *
     * @author Marcel
     */
    public function CargarDatosPlan($plan_downloading_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->find($plan_downloading_id);
        /** @var PlanDownloading $entity */
        if ($entity != null) {

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['plan'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarPlan: Elimina un plan downloading en la BD
     * @param int $plan_downloading_id Id
     * @author Marcel
     */
    public function EliminarPlan($plan_downloading_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->find($plan_downloading_id);
        /**@var PlanDownloading $entity */
        if ($entity != null) {

            // estimates
            $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                ->ListarEstimatesDePlanDownloading($plan_downloading_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The plan downloading could not be deleted, because it is related to a project estimate";
                return $resultado;
            }

            $descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Plan Downloading";
            $log_descripcion = "The plan downloading is deleted: $descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarPlans: Elimina los plans downloading seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarPlans($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $plan_downloading_id) {
                if ($plan_downloading_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
                        ->find($plan_downloading_id);
                    /** @var PlanDownloading $entity */
                    if ($entity != null) {

                        // estimates
                        $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                            ->ListarEstimatesDePlanDownloading($plan_downloading_id);
                        if (count($estimates) == 0) {
                            $descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Plan Downloading";
                            $log_descripcion = "The plan downloading is deleted: $descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }

                    }
                }
            }
        }
        $em->flush();

        if ($cant_eliminada == 0) {
            $resultado['success'] = false;
            $resultado['error'] = "The plan status could not be deleted, because they are associated with a project estimates";
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? "The operation was successful" : "The operation was successful. But attention, it was not possible to delete all the selected status because they are associated with a project estimates";
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarPlan: Actuializa los datos del plan downloading en la BD
     * @param int $plan_downloading_id Id
     * @author Marcel
     */
    public function ActualizarPlan($plan_downloading_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->find($plan_downloading_id);
        /** @var PlanDownloading $entity */
        if ($entity != null) {

            //Verificar name
            $plan = $this->getDoctrine()->getRepository(PlanDownloading::class)
                ->findOneBy(['description' => $description]);
            if ($plan != null && $entity->getPlanDownloadingId() != $plan->getPlanDownloadingId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The plan status name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Plan Downloading";
            $log_descripcion = "The plan downloading is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['plan_downloading_id'] = $entity->getPlanDownloadingId();

            return $resultado;
        }
    }

    /**
     * SalvarPlan: Guarda los datos de plan en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarPlan($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $plan = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->findOneBy(['description' => $description]);
        if ($plan != null ) {
            $resultado['success'] = false;
            $resultado['error'] = "The plan downloading name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new PlanDownloading();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Plan Downloading";
        $log_descripcion = "The plan downloading is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['plan_downloading_id'] = $entity->getPlanDownloadingId();

        return $resultado;
    }

    /**
     * ListarPlans: Listar los plans downloading
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarPlans($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $arreglo_resultado = array();
        $cont = 0;

        $lista = $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->ListarPlans($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        foreach ($lista as $value) {
            $plan_id = $value->getPlanDownloadingId();

            $acciones = $this->ListarAcciones($plan_id);

            $arreglo_resultado[$cont] = array(
                "id" => $plan_id,
                "description" => $value->getDescription(),
                "status" => $value->getStatus() ? 1 : 0,
                "acciones" => $acciones
            );


            $cont++;
        }

        return $arreglo_resultado;
    }

    /**
     * TotalPlans: Total de plans
     * @param string $sSearch Para buscar
     * @author Marcel
     */
    public function TotalPlans($sSearch)
    {
        return $this->getDoctrine()->getRepository(PlanDownloading::class)
            ->TotalPlans($sSearch);
    }

    /**
     * ListarAcciones: Lista los permisos de un usuario de la BD
     *
     * @author Marcel
     */
    public function ListarAcciones($id)
    {
        $usuario = $this->getUser();
        $permiso = $this->BuscarPermiso($usuario->getUsuarioId(), 30);

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