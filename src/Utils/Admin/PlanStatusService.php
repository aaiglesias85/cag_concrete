<?php

namespace App\Utils\Admin;

use App\Entity\Estimate;
use App\Entity\PlanStatus;
use App\Utils\Base;

class PlanStatusService extends Base
{
    /**
     * CargarDatosStatus: Carga los datos de un status
     *
     * @param int $status_id Id
     *
     * @author Marcel
     */
    public function CargarDatosStatus($status_id)
    {
        $resultado = array();
        $arreglo_resultado = array();

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->find($status_id);
        /** @var PlanStatus $entity */
        if ($entity != null) {

            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['status'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarStatus: Elimina un status en la BD
     * @param int $status_id Id
     * @author Marcel
     */
    public function EliminarStatus($status_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->find($status_id);
        /**@var PlanStatus $entity */
        if ($entity != null) {

            // estimates
            $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                ->ListarEstimatesDePlanStatus($status_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = "The plan status could not be deleted, because it is related to a project estimate";
                return $resultado;
            }

            $status_descripcion = $entity->getDescription();


            $em->remove($entity);
            $em->flush();

            //Salvar log
            $log_operacion = "Delete";
            $log_categoria = "Plan Status";
            $log_descripcion = "The plan status is deleted: $status_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = "The requested record does not exist";
        }

        return $resultado;
    }

    /**
     * EliminarStatuss: Elimina los statuss seleccionados en la BD
     * @param int $ids Ids
     * @author Marcel
     */
    public function EliminarStatuss($ids)
    {
        $em = $this->getDoctrine()->getManager();

        if ($ids != "") {
            $ids = explode(',', $ids);
            $cant_eliminada = 0;
            $cant_total = 0;
            foreach ($ids as $status_id) {
                if ($status_id != "") {
                    $cant_total++;
                    $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
                        ->find($status_id);
                    /** @var PlanStatus $entity */
                    if ($entity != null) {

                        // estimates
                        $estimates = $this->getDoctrine()->getRepository(Estimate::class)
                            ->ListarEstimatesDePlanStatus($status_id);
                        if (count($estimates) == 0) {
                            $status_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            $cant_eliminada++;

                            //Salvar log
                            $log_operacion = "Delete";
                            $log_categoria = "Plan Status";
                            $log_descripcion = "The plan status is deleted: $status_descripcion";
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
     * ActualizarStatus: Actuializa los datos del status en la BD
     * @param int $status_id Id
     * @author Marcel
     */
    public function ActualizarStatus($status_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->find($status_id);
        /** @var PlanStatus $entity */
        if ($entity != null) {

            //Verificar name
            $plan = $this->getDoctrine()->getRepository(PlanStatus::class)
                ->findOneBy(['description' => $description]);
            if ($plan != null && $entity->getStatusId() != $plan->getStatusId()) {
                $resultado['success'] = false;
                $resultado['error'] = "The plan status name is in use, please try entering another one.";
                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            //Salvar log
            $log_operacion = "Update";
            $log_categoria = "Plan Status";
            $log_descripcion = "The plan status is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['status_id'] = $entity->getStatusId();

            return $resultado;
        }
    }

    /**
     * SalvarStatus: Guarda los datos de status en la BD
     * @param string $description Nombre
     * @author Marcel
     */
    public function SalvarStatus($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        //Verificar name
        $plan = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->findOneBy(['description' => $description]);
        if ($plan != null ) {
            $resultado['success'] = false;
            $resultado['error'] = "The plan status name is in use, please try entering another one.";
            return $resultado;
        }

        $entity = new PlanStatus();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        //Salvar log
        $log_operacion = "Add";
        $log_categoria = "Plan Status";
        $log_descripcion = "The plan status is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['status_id'] = $entity->getStatusId();

        return $resultado;
    }

    /**
     * ListarStatus: Listar los statuss
     *
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarStatus($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        $resultado = $this->getDoctrine()->getRepository(PlanStatus::class)
            ->ListarStatusConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $status_id = $value->getStatusId();

            $data[] = array(
                "id" => $status_id,
                "description" => $value->getDescription(),
                "status" => $value->getStatus() ? 1 : 0,
            );
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}