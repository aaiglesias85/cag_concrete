<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Http\DataTablesHelper;
use App\Utils\Admin\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    private const FUNCION_ID = 40;

    public function __construct(
        private readonly TaskService $taskService,
    ) {
    }

    public function index()
    {
        $usuario = $this->getUser();
        $permiso = $this->taskService->BuscarPermiso($usuario->getUsuarioId(), self::FUNCION_ID);
        if (count($permiso) > 0) {
            if ($permiso[0]['ver']) {
                return $this->render('admin/task/index.html.twig', [
                    'permiso' => $permiso[0],
                    'status_text_pending' => Task::STATUS_TEXT_PENDING,
                    'status_text_complete' => Task::STATUS_TEXT_COMPLETE,
                ]);
            }
        }

        return $this->redirectToRoute('denegado');
    }

    public function listar(Request $request)
    {
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
        $task_id = $request->get('task_id');
        $description = $request->get('description');
        $status = $request->get('status');
        $due_day = $request->get('due_day');
        $usuario_id = $request->get('usuario_id');

        try {
            if ($task_id === '' || $task_id === null) {
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

    public function cambiarEstado(Request $request)
    {
        $task_id = $request->get('task_id');
        $status = (string) $request->get('status', '');
        try {
            $resultado = $this->taskService->CambiarEstadoTask($task_id, $status);
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
