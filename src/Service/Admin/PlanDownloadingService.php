<?php

namespace App\Service\Admin;

use App\Dto\Admin\PlanDownloading\PlanDownloadingActualizarRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingIdRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingIdsRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingListarRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingSalvarRequest;
use App\Entity\Estimate;
use App\Entity\PlanDownloading;
use App\Repository\EstimateRepository;
use App\Repository\PlanDownloadingRepository;
use App\Service\Base\Base;

class PlanDownloadingService extends Base
{
    /**
     * CargarDatosPlan: Carga los datos de un plan downloading.
     *
     * @author Marcel
     */
    public function CargarDatosPlan(PlanDownloadingIdRequest $dto)
    {
        $resultado = [];
        $arreglo_resultado = [];

        $plan_downloading_id = $dto->plan_downloading_id;
        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
           ->find($plan_downloading_id);
        /** @var PlanDownloading $entity */
        if (null != $entity) {
            $arreglo_resultado['description'] = $entity->getDescription();
            $arreglo_resultado['status'] = $entity->getStatus();

            $resultado['success'] = true;
            $resultado['plan'] = $arreglo_resultado;
        }

        return $resultado;
    }

    /**
     * EliminarPlan: Elimina un plan downloading en la BD.
     *
     * @author Marcel
     */
    public function EliminarPlan(PlanDownloadingIdRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();
        $plan_downloading_id = $dto->plan_downloading_id;

        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
           ->find($plan_downloading_id);
        /** @var PlanDownloading $entity */
        if (null != $entity) {
            // estimates
            /** @var EstimateRepository $estimateRepo */
            $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
            $estimates = $estimateRepo->ListarEstimatesDePlanDownloading($plan_downloading_id);
            if (count($estimates) > 0) {
                $resultado['success'] = false;
                $resultado['error'] = 'The plan downloading could not be deleted, because it is related to a project estimate';

                return $resultado;
            }

            $descripcion = $entity->getDescription();

            $em->remove($entity);
            $em->flush();

            // Salvar log
            $log_operacion = 'Delete';
            $log_categoria = 'Plan Downloading';
            $log_descripcion = "The plan downloading is deleted: $descripcion";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    /**
     * EliminarPlans: Elimina los plans downloading seleccionados en la BD.
     *
     * @author Marcel
     */
    public function EliminarPlans(PlanDownloadingIdsRequest $dto)
    {
        $em = $this->getDoctrine()->getManager();

        $ids = (string) ($dto->ids ?? '');
        $cant_eliminada = 0;
        $cant_total = 0;
        if ('' != $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $plan_downloading_id) {
                if ('' != $plan_downloading_id) {
                    ++$cant_total;
                    $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
                       ->find($plan_downloading_id);
                    /** @var PlanDownloading $entity */
                    if (null != $entity) {
                        // estimates
                        /** @var EstimateRepository $estimateRepo */
                        $estimateRepo = $this->getDoctrine()->getRepository(Estimate::class);
                        $estimates = $estimateRepo->ListarEstimatesDePlanDownloading($plan_downloading_id);
                        if (0 == count($estimates)) {
                            $descripcion = $entity->getDescription();

                            $em->remove($entity);
                            ++$cant_eliminada;

                            // Salvar log
                            $log_operacion = 'Delete';
                            $log_categoria = 'Plan Downloading';
                            $log_descripcion = "The plan downloading is deleted: $descripcion";
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
     * ActualizarPlan: Actuializa los datos del plan downloading en la BD.
     *
     * @author Marcel
     */
    public function ActualizarPlan(PlanDownloadingActualizarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $plan_downloading_id = (int) $d->plan_downloading_id;
        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);

        $entity = $this->getDoctrine()->getRepository(PlanDownloading::class)
           ->find($plan_downloading_id);
        /** @var PlanDownloading $entity */
        if (null != $entity) {
            // Verificar name
            $plan = $this->getDoctrine()->getRepository(PlanDownloading::class)
               ->findOneBy(['description' => $description]);
            if (null != $plan && $entity->getPlanDownloadingId() != $plan->getPlanDownloadingId()) {
                $resultado['success'] = false;
                $resultado['error'] = 'The plan status name is in use, please try entering another one.';

                return $resultado;
            }

            $entity->setDescription($description);
            $entity->setStatus($status);

            $em->flush();

            // Salvar log
            $log_operacion = 'Update';
            $log_categoria = 'Plan Downloading';
            $log_descripcion = "The plan downloading is modified: $description";
            $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

            $resultado['success'] = true;
            $resultado['plan_downloading_id'] = $entity->getPlanDownloadingId();

            return $resultado;
        }

        $resultado['success'] = false;
        $resultado['error'] = 'The requested record does not exist';

        return $resultado;
    }

    /**
     * SalvarPlan: Guarda los datos de plan en la BD.
     *
     * @author Marcel
     */
    public function SalvarPlan(PlanDownloadingSalvarRequest $d)
    {
        $em = $this->getDoctrine()->getManager();

        $description = (string) $d->description;
        $status = $this->parseBooleanStatus((string) $d->status);

        // Verificar name
        $plan = $this->getDoctrine()->getRepository(PlanDownloading::class)
           ->findOneBy(['description' => $description]);
        if (null != $plan) {
            $resultado['success'] = false;
            $resultado['error'] = 'The plan downloading name is in use, please try entering another one.';

            return $resultado;
        }

        $entity = new PlanDownloading();

        $entity->setDescription($description);
        $entity->setStatus($status);

        $em->persist($entity);

        $em->flush();

        // Salvar log
        $log_operacion = 'Add';
        $log_categoria = 'Plan Downloading';
        $log_descripcion = "The plan downloading is added: $description";
        $this->SalvarLog($log_operacion, $log_categoria, $log_descripcion);

        $resultado['success'] = true;
        $resultado['plan_downloading_id'] = $entity->getPlanDownloadingId();

        return $resultado;
    }

    /**
     * ListarPlans: Listar los plans downloading.
     *
     * @author Marcel
     */
    public function ListarPlans(PlanDownloadingListarRequest $listar)
    {
        $dt = $listar->dt;

        /** @var PlanDownloadingRepository $planDownloadingRepo */
        $planDownloadingRepo = $this->getDoctrine()->getRepository(PlanDownloading::class);
        $resultado = $planDownloadingRepo->ListarPlansConTotal(
            $dt['start'],
            $dt['length'],
            $dt['search'],
            $dt['orderField'],
            $dt['orderDir']
        );

        $data = [];

        foreach ($resultado['data'] as $value) {
            $plan_id = $value->getPlanDownloadingId();

            $data[] = [
                'id' => $plan_id,
                'description' => $value->getDescription(),
                'status' => $value->getStatus() ? 1 : 0,
            ];
        }

        return [
            'data' => $data,
            'total' => $resultado['total'], // ya viene con el filtro aplicado
        ];
    }

    private function parseBooleanStatus(string $status): bool
    {
        return filter_var($status, FILTER_VALIDATE_BOOLEAN);
    }
}
