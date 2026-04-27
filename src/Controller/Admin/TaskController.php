<?php

namespace App\Controller\Admin;

use App\Constants\FunctionId;
use App\Entity\Task;
use App\Http\DataTablesHelper;
use App\Service\Admin\AdminAccessService;
use App\Service\Admin\WidgetAccessService;
use App\Utils\Admin\DefaultService;
use App\Utils\Admin\TaskService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractAdminController
{
    public function __construct(
        AdminAccessService $adminAccess,
        private readonly TaskService $taskService,
        private readonly DefaultService $defaultService,
        private readonly WidgetAccessService $widgetAccessService,
    ) {
        parent::__construct($adminAccess);
    }

    public function index()
    {
        $acceso = $this->adminAccess->exigirUsuarioYPermisoVer($this->getUser(), FunctionId::TASKS);
        if ($acceso instanceof RedirectResponse) {
            return $acceso;
        }
        $usuario = $acceso['usuario'];
        $permisos = $acceso['permisos'];
        $permiso = $permisos[0] ?? ['ver' => false, 'agregar' => false, 'editar' => false, 'eliminar' => false, 'funcion_id' => FunctionId::TASKS, 'permiso_id' => 0];

        return $this->render('admin/task/index.html.twig', [
            'permiso' => $permiso,
            'status_text_pending' => Task::STATUS_TEXT_PENDING,
            'status_text_complete' => Task::STATUS_TEXT_COMPLETE,
        ]);
    }

    public function listar(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        if (!$this->isTasksFunctionGranted($g->getUsuarioId())) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        try {
            $dt = DataTablesHelper::parse(
                $request,
                allowedOrderFields: ['id', 'description', 'due_date', 'status', 'created_at', 'assigned'],
                defaultOrderField: 'due_date',
            );

            $fecha_inicial = $request->get('fechaInicial');
            $fecha_fin = $request->get('fechaFin');
            $statusFiltro = (string) $request->get('statusFiltro', '');
            $usuarioFiltro = (string) $request->get('usuarioFiltro', '');

            $result = $this->taskService->ListarTasks(
                $dt['start'],
                $dt['length'],
                $dt['search'],
                $dt['orderField'],
                $dt['orderDir'],
                $fecha_inicial,
                $fecha_fin,
                $statusFiltro,
                $usuarioFiltro,
            );

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

    public function salvar(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        if (!$this->isTasksFunctionGranted($g->getUsuarioId())) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        $u = $g;
        $task_id = $request->get('task_id');
        $description = $request->get('description');
        $status = $request->get('status');
        $due_day = $request->get('due_day');
        $usuario_id = $request->get('usuario_id');

        try {
            if ('' === $task_id || null === $task_id) {
                $resultado = $this->taskService->SalvarTask($description, $status, $due_day, $usuario_id);
            } else {
                $resultado = $this->taskService->ActualizarTask($task_id, $description, $status, $due_day, $usuario_id);
            }

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

    public function eliminar(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        if (!$this->isTasksFunctionGranted($g->getUsuarioId())) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        $u = $g;
        $task_id = $request->get('task_id');
        try {
            $resultado = $this->taskService->EliminarTask($task_id);
            if ($resultado['success']) {
                return $this->json(['success' => true, 'message' => 'The operation was successful']);
            }

            return $this->json(['success' => false, 'error' => $resultado['error']]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function eliminarTasks(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        if (!$this->isTasksFunctionGranted($g->getUsuarioId())) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        $u = $g;
        $ids = $request->get('ids');
        try {
            $resultado = $this->taskService->EliminarTasks($ids);
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

    public function cargarDatos(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        if (!$this->isTasksFunctionGranted($g->getUsuarioId())) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
        }
        $u = $g;
        $task_id = $request->get('task_id');
        try {
            $resultado = $this->taskService->CargarDatosTask($task_id);
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

    public function listarHome(Request $request)
    {
        $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
        if ($g instanceof RedirectResponse) {
            return $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        $usuario = $g;
        if (!$this->widgetAccessService->isWidgetEnabledForUser($usuario->getUsuarioId(), 'tasks')) {
            return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
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

    public function cambiarEstado(Request $request)
    {
        $task_id = $request->get('task_id');
        $status = (string) $request->get('status', '');
        try {
            $g = $this->adminAccess->exigirUsuarioOlogin($this->getUser());
            if ($g instanceof RedirectResponse) {
                return $this->json(['success' => false, 'error' => 'Unauthenticated'], 401);
            }
            $usuario = $g;
            if (!$this->isTasksFunctionGranted($usuario->getUsuarioId())) {
                return $this->json(['success' => false, 'error' => 'Not allowed'], 403);
            }
            $pT = $this->defaultService->BuscarPermiso($usuario->getUsuarioId(), FunctionId::TASKS);
            $perm = $pT[0] ?? ['ver' => false, 'agregar' => false, 'editar' => false, 'eliminar' => false, 'funcion_id' => FunctionId::TASKS, 'permiso_id' => 0];
            $resultado = $this->taskService->CambiarEstadoTask($task_id, $status, $usuario, $perm);
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

    private function isTasksFunctionGranted(int $userId): bool
    {
        $p = $this->adminAccess->buscarPermisosMismoBase($userId, FunctionId::TASKS);
        if (\count($p) < 1) {
            return false;
        }
        if (empty($p[0]['ver'])) {
            return false;
        }

        return true;
    }
}
