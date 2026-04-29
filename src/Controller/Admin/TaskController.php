<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Dto\Admin\Task\TaskActualizarRequest;
use App\Dto\Admin\Task\TaskCambiarEstadoRequest;
use App\Dto\Admin\Task\TaskIdRequest;
use App\Dto\Admin\Task\TaskIdsRequest;
use App\Dto\Admin\Task\TaskListarRequest;
use App\Dto\Admin\Task\TaskSalvarRequest;
use App\Entity\Task;
use App\Security\AdminPermission;
use App\Security\Attribute\RequireAdminPermission;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\DefaultService;
use App\Service\Admin\TaskService;
use App\Service\Admin\WidgetAccessService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractAdminController
{
    public function __construct(
        AdminAccessService $adminAccess,
        private readonly TaskService $taskService,
        private readonly DefaultService $defaultService,
        private readonly WidgetAccessService $widgetAccessService)
    {
        parent::__construct($adminAccess);
    }

    #[RequireAdminPermission(FunctionId::TASKS)]
    public function index()
    {
        $usuario = $this->DevolverUsuario();
        $permisos = $this->adminAccess->buscarPermisosMismoBase($usuario->getUsuarioId(), FunctionId::TASKS);
        $permiso = $permisos[0] ?? throw new \LogicException('Permiso TASKS esperado tras #[RequireAdminPermission].');

        return $this->render('admin/task/index.html.twig', [
            'permiso' => $permiso,
            'status_text_pending' => Task::STATUS_TEXT_PENDING,
            'status_text_complete' => Task::STATUS_TEXT_COMPLETE,
        ]);
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::View, jsonOnDenied: true)]
    public function listar(TaskListarRequest $listar): JsonResponse
    {
        try {
            $dt = $listar->dt;

            $result = $this->taskService->ListarTasks($listar);

            return $this->json([
                'draw' => $dt['draw'],
                'data' => $result['data'],
                'recordsTotal' => (int) $result['total'],
                'recordsFiltered' => (int) $result['total'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::Add, jsonOnDenied: true)]
    public function salvar(TaskSalvarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->taskService->SalvarTask($d);

            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'message' => 'The operation was successful',
                    'task_id' => $resultado['task_id'] ?? null,
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Error',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::Edit, jsonOnDenied: true)]
    public function actualizar(TaskActualizarRequest $d): JsonResponse
    {
        try {
            $resultado = $this->taskService->ActualizarTask($d);

            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'message' => 'The operation was successful',
                    'task_id' => $resultado['task_id'] ?? null,
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Error',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminar(TaskIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->taskService->EliminarTask($dto);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::Delete, jsonOnDenied: true)]
    public function eliminarTasks(TaskIdsRequest $idsDto): JsonResponse
    {
        try {
            $resultado = $this->taskService->EliminarTasks($idsDto);
            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'message' => $resultado['message'] ?? 'The operation was successful',
                ]);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::View, jsonOnDenied: true)]
    public function cargarDatos(TaskIdRequest $dto): JsonResponse
    {
        try {
            $resultado = $this->taskService->CargarDatosTask($dto);
            if (isset($resultado['success']) && $resultado['success']) {
                return $this->json([
                    'success' => true,
                    'task' => $resultado['task'],
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'The requested record does not exist',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Widget home: `HOME` + usuario; datos filtrados por widget `tasks` y permisos TASKS en payload (sin exigir ver TASKS para ver cabecera vacía).
     */
    #[RequireAdminPermission(FunctionId::HOME, AdminPermission::View, jsonOnDenied: true)]
    public function listarHome(Request $request): JsonResponse
    {
        $usuario = $this->DevolverUsuario();
        if (!$this->widgetAccessService->isWidgetVisibleOnHome($usuario->getUsuarioId(), 'tasks')) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], Response::HTTP_FORBIDDEN);
        }
        $pTask = $this->defaultService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::TASKS);
        $perm = $pTask[0] ?? ['ver' => false, 'agregar' => false, 'editar' => false, 'eliminar' => false, 'funcion_id' => FunctionId::TASKS, 'permiso_id' => 0];
        try {
            $period = (string) $request->query->get('period', 'current_month');
            $fi = (string) $request->query->get('fechaInicial', '');
            $ff = (string) $request->query->get('fechaFin', '');
            $rango = $this->taskService->resolverRangoFechasPeriodo($period, $fi, $ff);
            $tasks = $this->taskService->listarTareasPayloadHome($usuario, $perm, $rango['inicial'], $rango['final']);

            return $this->json([
                'success' => true,
                'tasks' => $tasks,
                'range' => $rango,
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[RequireAdminPermission(FunctionId::TASKS, AdminPermission::Edit, jsonOnDenied: true)]
    public function cambiarEstado(TaskCambiarEstadoRequest $d): JsonResponse
    {
        try {
            $usuario = $this->DevolverUsuario();
            $pT = $this->defaultService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::TASKS);
            $perm = $pT[0] ?? ['ver' => false, 'agregar' => false, 'editar' => false, 'eliminar' => false, 'funcion_id' => FunctionId::TASKS, 'permiso_id' => 0];
            $resultado = $this->taskService->CambiarEstadoTask($d, $usuario, $perm);
            if ($resultado['success']) {
                return $this->json([
                    'success' => true,
                    'message' => $resultado['message'] ?? 'The operation was successful',
                ]);
            }

            return $this->json([
                'success' => false,
                'error' => $resultado['error'] ?? 'Error',
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
