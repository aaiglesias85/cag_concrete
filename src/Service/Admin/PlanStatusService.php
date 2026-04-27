<?php

namespace App\Service\Admin;

use App\Entity\Estimate;
use App\Entity\PlanStatus;
use App\Repository\EstimateRepository;
use App\Repository\PlanStatusRepository;
use App\Service\Base\Base;

class PlanStatusService extends Base
{
    /**
     * CargarDatosStatus: Carga los datos de un status.
     *
     * @param int $status_id Id
     *
     * @author Marcel
     */
    public function CargarDatosStatus($status_id)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
           ->find($status_id);
        /** @var PlanStatus $entity */
        if (null != $entity) {
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['status'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarStatus: Elimina un status en la BD.
     *
     * @param int $status_id Id
     *
     * @author Marcel
     */
    public function EliminarStatus($status_id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
           ->find($status_id);
        /** @var PlanStatus $entity */
        if (null != $entity) {
            // estimates
            /** @var EstimateRepository $estimateRepo */
            $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
            $estimates = $estimateRepo->ListarEstimatesDePlanStatus($status_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The plan status could not be deleted, because it is related to a project estimate';

                return $resultado;
            }

            $status_descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Plan Status';
            $log_descripcion = "The plan status is deleted: $status_descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarStatuss: Elimina los statuss seleccionados en la BD.
     *
     * @param int $ids Ids
     *
     * @author Marcel
     */
    public function EliminarStatuss($ids)
    {
        $em = $this->getDoctrine()->getManager();

        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', (string) $ids);
            foreach ($ids as $status_id) {
                if ('' != $status_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
                       ->find($status_id);
                    /** @var PlanStatus $entity */
                    if (null != $entity) {
                        // estimates
                        /** @var EstimateRepository $estimateRepo */
                        $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
                        $estimates = $estimateRepo->ListarEstimatesDePlanStatus($status_id);
                        if (0 == count($estimates)) {
                            $status_descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Plan Status';
                            $log_descripcion = "The plan status is deleted: $status_descripcion";
                            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);
                        }
                    }
                }
            }
        }
        $em->flush();

        if (0 == $cant_eliminada) {
            $resultado['success'] = false;
            $resultado['error'] = 'The plan status could not be deleted, because they are associated with a project estimates';
        } else {
            $resultado['success'] = true;

            $mensaje = ($cant_eliminada == $cant_total) ? 'The operation was successful' : 'The operation was successful. But attention, it was not possible to delete all the selected status because they are associated with a project estimates';
            $resultado['message'] = $mensaje;
        }

        return $resultado;
    }

    /**
     * ActualizarStatus: Actuializa los datos del status en la BD.
     *
     * @param int $status_id Id
     *
     * @author Marcel
     */
    public function ActualizarStatus($status_id, $description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()->getRepository(PlanStatus::class)
           ->find($status_id);
        /** @var PlanStatus $entity */
        if (null != $entity) {
            // Verificar name
            $plan = $this->getDoctrine()->getRepository(PlanStatus::class)
               ->findOneBy(['description' => $description]);
            if (null != $plan && $entity->getStatusId() != $plan->getStatusId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The plan status name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Plan Status';
            $log_descripcion = "The plan status is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['status_id'] = $entity->getStatusId();

            return $resultado;
        }
    }

    /**
     * SalvarStatus: Guarda los datos de status en la BD.
     *
     * @param string $description Nombre
     *
     * @author Marcel
     */
    public function SalvarStatus($description, $status)
    {
        $em = $this->getDoctrine()->getManager();

        // Verificar name
        $plan = $this->getDoctrine()->getRepository(PlanStatus::class)
           ->findOneBy(['description' => $description]);
        if (null != $plan) {
            $resultado['success'] = false;
            $resultado['error'] = 'The plan status name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new PlanStatus();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Plan Status';
        $log_descripcion = "The plan status is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['status_id'] = $entity->getStatusId();

        return $resultado;
    }

    /**
     * ListarStatus: Listar los statuss.
     *
     * @param int    $start   Inicio
     * @param int    $limit   Limite
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function ListarStatus($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0)
    {
        /** @var PlanStatusRepository $planStatusRepo */
        $planStatusRepo = $this->getDoctrine()->getRepository(PlanStatus::class);
        $resultado = $planStatusRepo->ListarStatusConTotal($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0);

        $data = [];

        foreach ($resultado['data'] as $value) {
            $status_id = $value->getStatusId();

            $data[] = [
                'id' => $status_id,
                'description' => $value->getDescription(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }
}
