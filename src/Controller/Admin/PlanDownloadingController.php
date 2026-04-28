<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\PlanDownloading\PlanDownloadingActualizarRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingIdRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingIdsRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingListarRequest;
use App\Dto\Admin\PlanDownloading\PlanDownloadingSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\PlanDownloadingService;
use Symfony\Component\HttpFoundation\JsonResponse;
class PlanDownloadingController extends AbstractAdminController
{
    private $planDownloadingService;

    public function __construct(
        AdminAccessService $adminAccess,
        PlanDownloadingService $planDownloadingService) {
        parent::__construct($adminAccess);
        $this->planDownloadingService = $planDownloadingService;
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::PLAN_DOWNLOADING);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso PLAN_DOWNLOADING esperado tras #[RequireAdminPermission].');

        return $this->render('admin/plan-downloading/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::View, jsonOnDenied: true)]
    public function listar(PlanDownloadingListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->planDownloadingService->ListarPlans(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir']
            );

            $resultadoJson = [
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(PlanDownloadingSalvarRequest $d): JsonResponse
    {
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->planDownloadingService->SalvarPlan($description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['plan_downloading_id'] = $resultado['plan_downloading_id'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(PlanDownloadingActualizarRequest $d): JsonResponse
    {
        $plan_downloading_id = (string) $d->plan_downloading_id;
        $description = (string) $d->description;
        $status = (string) $d->status;

        try {
            $resultado = $this->planDownloadingService->ActualizarPlan($plan_downloading_id, $description, $status);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['plan_downloading_id'] = $resultado['plan_downloading_id'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(PlanDownloadingIdRequest $dto): JsonResponse
    {
        $plan_downloading_id = $dto->plan_downloading_id;

        try {
            $resultado = $this->planDownloadingService->EliminarPlan($plan_downloading_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarPlans(PlanDownloadingIdsRequest $idsDto): JsonResponse
    {
        $ids = (string) $idsDto->ids;

        try {
            $resultado = $this->planDownloadingService->EliminarPlans($ids);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::PLAN_DOWNLOADING, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(PlanDownloadingIdRequest $dto): JsonResponse
    {
        $plan_downloading_id = $dto->plan_downloading_id;

        try {
            $resultado = $this->planDownloadingService->CargarDatosPlan($plan_downloading_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['plan'] = $resultado['plan'];

                return $this->json($resultadoJson);
            }
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }
}
