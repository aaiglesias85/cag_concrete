<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Perfil\PerfilActualizarRequest;
use App\Dto\Admin\Perfil\PerfilIdRequest;
use App\Dto\Admin\Perfil\PerfilIdsRequest;
use App\Dto\Admin\Perfil\PerfilListarRequest;
use App\Dto\Admin\Perfil\PerfilSalvarRequest;
use App\Entity\Funcion;
use App\Entity\Widget;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\FuncionPermissionUiGrouping;
use App\Service\Admin\PerfilService;
use Symfony\Component\HttpFoundation\JsonResponse;
class PerfilController extends AbstractAdminController
{
    private $perfilService;

    private FuncionPermissionUiGrouping $funcionPermissionUiGrouping;

    public function __construct(
        AdminAccessService $adminAccess,
        PerfilService $perfilService,
        FuncionPermissionUiGrouping $funcionPermissionUiGrouping) {
        parent::__construct($adminAccess);
        $this->perfilService = $perfilService;
        $this->funcionPermissionUiGrouping = $funcionPermissionUiGrouping;
    }

    #[RequireAdminPermission(FunctionId::ROL)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::ROL);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso ROL esperado tras #[RequireAdminPermission].');

        $funciones = $this->perfilService->getDoctrine()->getRepository(Funcion::class)
            ->ListarOrdenados();
        $funcionesAgrupadas = $this->funcionPermissionUiGrouping->group($funciones);

        $widgets = $this->perfilService->getDoctrine()->getRepository(Widget::class)
            ->findAllOrdered();

        return $this->render('admin/rol/index.html.twig', [
            'funciones' => $funciones,
            'funcionesAgrupadas' => $funcionesAgrupadas,
            'widgets' => $widgets,
            'permiso' => $permiso,
        ]);
    }

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::View, jsonOnDenied: true)]
    public function listar(PerfilListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->perfilService->ListarPerfiles(
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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(PerfilSalvarRequest $d): JsonResponse
    {
        $descripcion = (string) $d->descripcion;
        $permisos = json_decode((string) $d->permisos);
        $waRaw = $d->widget_access;
        $widgetAccess = is_string($waRaw) && '' !== $waRaw ? json_decode($waRaw, true) : null;

        try {
            $resultado = $this->perfilService->SalvarPerfil($descripcion, $permisos, is_array($widgetAccess) ? $widgetAccess : null);

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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(PerfilActualizarRequest $d): JsonResponse
    {
        $perfil_id = (string) $d->perfil_id;
        $descripcion = (string) $d->descripcion;
        $permisos = json_decode((string) $d->permisos);
        $waRaw = $d->widget_access;
        $widgetAccess = is_string($waRaw) && '' !== $waRaw ? json_decode($waRaw, true) : null;

        try {
            $resultado = $this->perfilService->ActualizarPerfil($perfil_id, $descripcion, $permisos, is_array($widgetAccess) ? $widgetAccess : null);

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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(PerfilIdRequest $dto): JsonResponse
    {
        $perfil_id = $dto->perfil_id;

        try {
            $resultado = $this->perfilService->EliminarPerfil($perfil_id);
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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarPerfiles(PerfilIdsRequest $dto): JsonResponse
    {
        $ids = $dto->ids;

        try {
            $resultado = $this->perfilService->EliminarPerfiles($ids);
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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(PerfilIdRequest $dto): JsonResponse
    {
        $perfil_id = $dto->perfil_id;

        try {
            $resultado = $this->perfilService->CargarDatosPerfil($perfil_id);
            if ($resultado['success']) {
                $resultadoJson['success'] = $resultado['success'];
                $resultadoJson['perfil'] = $resultado['perfil'];

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

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::View, jsonOnDenied: true)]
    public function listarPermisos(PerfilIdRequest $dto): JsonResponse
    {
        $perfil_id = $dto->perfil_id;

        try {
            $permisos = $this->perfilService->ListarPermisosDePerfil($perfil_id);

            $resultadoJson['success'] = true;
            $resultadoJson['permisos'] = $permisos;

            return $this->json($resultadoJson);
        } catch (\Exception $e) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $e->getMessage();

            return $this->json($resultadoJson);
        }
    }

    #[RequireAdminPermission(FunctionId::ROL, AdminPermission::View, jsonOnDenied: true)]
    public function listarWidgetPreferences(PerfilIdRequest $dto): JsonResponse
    {
        $perfil_id = $dto->perfil_id;
        try {
            $widgets = $this->perfilService->listarWidgetPreferencesDePerfil($perfil_id);

            return $this->json(['success' => true, 'widgets' => $widgets]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
