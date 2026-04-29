<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Inspector\InspectorActualizarRequest;
use App\Dto\Admin\Inspector\InspectorIdRequest;
use App\Dto\Admin\Inspector\InspectorIdsRequest;
use App\Dto\Admin\Inspector\InspectorListarRequest;
use App\Dto\Admin\Inspector\InspectorSalvarRequest;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\InspectorService;
use Symfony\Component\HttpFoundation\JsonResponse;

class InspectorController extends AbstractAdminController
{
    private $inspectorService;

    public function __construct(
        AdminAccessService $adminAccess,
        InspectorService $inspectorService)
    {
        parent::__construct($adminAccess);
        $this->inspectorService = $inspectorService;
    }

    #[RequireAdminPermission(FunctionId::INSPECTOR)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::INSPECTOR);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso INSPECTOR esperado tras #[RequireAdminPermission].');

        return $this->render('admin/inspector/index.html.twig', [
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::View, jsonOnDenied: true)]
    public function listar(InspectorListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->inspectorService->ListarInspectors($listar);

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

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(InspectorSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->inspectorService->SalvarInspector($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['inspector_id'] = $resultado['inspector_id'];

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

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(InspectorActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->inspectorService->ActualizarInspector($d);

            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['message'] = 'The operation was successful';
                $resultadoJson['inspector_id'] = $resultado['inspector_id'];

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

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(InspectorIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->inspectorService->EliminarInspector($dto);
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

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarInspectors(InspectorIdsRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->inspectorService->EliminarInspectors($dto);
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

    #[RequireAdminPermission(FunctionId::INSPECTOR, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(InspectorIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->inspectorService->CargarDatosInspector($dto);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['inspector'] = $resultado['inspector'];

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
