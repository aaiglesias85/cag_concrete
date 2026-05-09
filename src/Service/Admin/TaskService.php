<?php

namespace App\Service\Admin;

use App\Dto\Admin\Task\TaskActualizarRequest;
use App\Dto\Admin\Task\TaskCambiarEstadoRequest;
use App\Dto\Admin\Task\TaskIdRequest;
use App\Dto\Admin\Task\TaskIdsRequest;
use App\Dto\Admin\Task\TaskListarRequest;
use App\Dto\Admin\Task\TaskSalvarRequest;
use App\Entity\Task;
use App\Entity\Usuario;
use App\Repository\TaskRepository;
use App\Service\Base\Base;

class TaskService extends Base
{
    public function CargarDatosTask(TaskIdRequest $dto): array
    {
        $resultado = [];
        $task_id = $dto->task_id;
        $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
        if ($entity instanceof Task) {
            $u = $entity->getAssignedUser();
            $assignedLabel = '';
            if (null !== $u) {
                $assignedLabel = $u->getNombreCompleto().'<'.($u->getEmail() ?? '').'>';
            }
            $resultado['success'] = true;
            $resultado['task'] = [
                'description' => $entity->getDescription(),
                'status' => $entity->getStatus(),
                'due_date' => $entity->getDueDate() ? $entity->getDueDate()->format('m/d/Y') : '',
                'usuario_id' => $u ? $u->getUsuarioId() : '',
                'assigned_label' => $assignedLabel,
                'created_at' => $entity->getCreatedAt() ? $entity->getCreatedAt()->format('m/d/Y H:i') : '',
            ];
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'The requested record does not exist';
        }

        return $resultado;
    }

    public function EliminarTask(TaskIdRequest $dto): array
    {
        $task_id = $dto->task_id;
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
        if ($entity instanceof Task) {
            $desc = mb_substr((string) $entity->getDescription(), 0, 80);
            $em->remove($entity);
            $em->flush();
            $this->SalvarLog('Delete', 'Task', "The task is deleted: $desc");

            return ['success' => true];
        }

        return ['success' => false, 'error' => 'The requested record does not exist'];
    }

    public function EliminarTasks(TaskIdsRequest $dto): array
    {
        $ids = $dto->ids;
        $em = $this->getDoctrine()->getManager();
        $cant_eliminada = 0;
        $cant_total = 0;
        if (!empty($ids)) {
            foreach (explode(',', (string) $ids) as $task_id) {
                if ('' === $task_id) {
                    continue;
                }
                ++$cant_total;
                $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
                if ($entity instanceof Task) {
                    $desc = mb_substr((string) $entity->getDescription(), 0, 80);
                    $em->remove($entity);
                    ++$cant_eliminada;
                    $this->SalvarLog('Delete', 'Task', "The task is deleted: $desc");
                }
            }
        }
        $em->flush();

        if (0 === $cant_eliminada) {
            return ['success' => false, 'error' => 'No tasks could be deleted'];
        }

        $mensaje = ($cant_eliminada === $cant_total)
            ? 'The operation was successful'
            : 'The operation was successful. Some selected tasks could not be deleted';

        return ['success' => true, 'message' => $mensaje];
    }

    public function ActualizarTask(TaskActualizarRequest $d): array
    {
        $task_id = $d->task_id;
        $description = (string) $d->description;
        $status = (string) $d->status;
        $due_day = (string) ($d->due_day ?? '');
        $usuario_id = (string) ($d->usuario_id ?? '');
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
        if (!$entity instanceof Task) {
            return ['success' => false, 'error' => 'The requested record does not exist'];
        }

        $entity->setDescription($description);
        $entity->setStatus($this->normalizarStatus($status));
        $entity->setDueDate($this->parseDueDate($due_day));
        $entity->setAssignedUser($this->resolverUsuario($usuario_id));

        $em->flush();

        $this->SalvarLog('Update', 'Task', 'The task is modified');

        return ['success' => true, 'task_id' => $entity->getTaskId()];
    }

    public function SalvarTask(TaskSalvarRequest $d): array
    {
        $description = (string) $d->description;
        $status = (string) $d->status;
        $due_day = (string) ($d->due_day ?? '');
        $usuario_id = (string) ($d->usuario_id ?? '');
        $em = $this->getDoctrine()->getManager();
        $entity = new Task();
        $entity->setDescription($description);
        $entity->setStatus($this->normalizarStatus($status));
        $entity->setDueDate($this->parseDueDate($due_day));
        $entity->setAssignedUser($this->resolverUsuario($usuario_id));
        $entity->setCreatedAt(new \DateTime());

        $em->persist($entity);
        $em->flush();

        $this->SalvarLog('Add', 'Task', 'The task is added');

        return ['success' => true, 'task_id' => $entity->getTaskId()];
    }

    /**
     * @return array{data: list<array<string, mixed>>, total: int}
     */
    public function ListarTasks(TaskListarRequest $listar): array
    {
        $dt = $listar->dt;
        /** @var TaskRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Task::class);
        $resultado = $repo->ListarTasksConTotal(
            (int) $dt['start'],
            (int) $dt['length'],
            $dt['search'],
            (string) $dt['orderField'],
            (string) $dt['orderDir'],
            (string) $listar->fecha_inicial,
            (string) $listar->fecha_fin,
            (string) $listar->statusFiltro,
            (string) $listar->usuarioFiltro,
            null,
        );

        $data = [];
        foreach ($resultado['data'] as $task) {
            $u = $task->getAssignedUser();
            $st = $task->getStatus();
            $data[] = [
                'id' => $task->getTaskId(),
                'description' => $task->getDescription(),
                'due_date' => $task->getDueDate() ? $task->getDueDate()->format('m/d/Y') : '',
                'assigned' => $u ? $u->getNombreCompleto() : '',
                'status' => $st,
                'status_label' => Task::getStatusLabel($st),
                'label_pending' => Task::getStatusLabel(Task::STATUS_PENDING),
                'label_complete' => Task::getStatusLabel(Task::STATUS_COMPLETE),
                'created_at' => $task->getCreatedAt() ? $task->getCreatedAt()->format('m/d/Y H:i') : '',
            ];
        }

        return ['data' => $data, 'total' => $resultado['total']];
    }

    /**
     * @return array{inicial: string, final: string}
     */
    public function resolverRangoFechasPeriodo(string $period, string $customFi, string $customFf): array
    {
        $p = strtolower(trim($period));
        if ('all' === $p || 'all_time' === $p || '' === $p) {
            return ['inicial' => '', 'final' => ''];
        }
        if ('custom' === $p) {
            return [
                'inicial' => trim($customFi),
                'final' => trim($customFf),
            ];
        }
        $now = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        if ('current_month' === $p) {
            $inicio = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
            $fin = (clone $now)->modify('last day of this month')->setTime(0, 0, 0);

            return [
                'inicial' => $inicio->format('m/d/Y'),
                'final' => $fin->format('m/d/Y'),
            ];
        }
        if ('last_month' === $p) {
            $inicio = (clone $now)->modify('first day of last month')->setTime(0, 0, 0);
            $fin = (clone $now)->modify('last day of last month')->setTime(0, 0, 0);

            return [
                'inicial' => $inicio->format('m/d/Y'),
                'final' => $fin->format('m/d/Y'),
            ];
        }

        $inicio = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
        $fin = (clone $now)->modify('last day of this month')->setTime(0, 0, 0);

        return [
            'inicial' => $inicio->format('m/d/Y'),
            'final' => $fin->format('m/d/Y'),
        ];
    }

    /**
     * Tasks for the home widget: each user only sees tasks assigned to them (perfil administrador no amplía la vista).
     *
     * @return list<array<string, mixed>>
     */
    public function listarTareasPayloadHome(Usuario $viewer, array $perm, string $fi, string $ff, int $limit = 30): array
    {
        // Even without full module access, show user their assigned tasks in the widget
        // Only restrict if they can't view/see tasks at all
        $only = (int) $viewer->getUsuarioId();

        /** @var TaskRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Task::class);
        $resultado = $repo->ListarTasksConTotal(
            0,
            $limit,
            null,
            'due_date',
            'desc',
            $fi,
            $ff,
            '',
            '',
            $only,
            true,  // includePendingWithoutDateRange: Show all pending tasks regardless of date
        );
        $list = [];
        foreach ($resultado['data'] as $task) {
            $u = $task->getAssignedUser();
            $st = $this->normalizarStatus($task->getStatus());
            $assignedId = $u ? (int) $u->getUsuarioId() : 0;
            $viewerId = (int) $viewer->getUsuarioId();
            $list[] = [
                'id' => $task->getTaskId(),
                'description' => $task->getDescription() ?? '',
                'due_date' => $task->getDueDate() ? $task->getDueDate()->format('m/d/Y') : '',
                'assigned' => $u ? $u->getNombreCompleto() : '',
                'show_assigned' => $assignedId > 0 && $assignedId !== $viewerId,
                'status' => $st,
                'status_label' => Task::getStatusLabel($st),
                'label_pending' => Task::getStatusLabel(Task::STATUS_PENDING),
                'label_complete' => Task::getStatusLabel(Task::STATUS_COMPLETE),
                'can_toggle_status' => $this->puedeMostrarAccionHecho($viewer, $perm, $task),
            ];
        }

        usort($list, static function (array $a, array $b): int {
            $aDone = Task::STATUS_COMPLETE === $a['status'] ? 1 : 0;
            $bDone = Task::STATUS_COMPLETE === $b['status'] ? 1 : 0;
            if ($aDone !== $bDone) {
                return $aDone - $bDone; // pending first, complete last
            }
            $aDue = !empty($a['due_date']) ? \DateTime::createFromFormat('m/d/Y', (string) $a['due_date']) : false;
            $bDue = !empty($b['due_date']) ? \DateTime::createFromFormat('m/d/Y', (string) $b['due_date']) : false;
            $aTs = $aDue instanceof \DateTimeInterface ? (int) $aDue->format('U') : 0;
            $bTs = $bDue instanceof \DateTimeInterface ? (int) $bDue->format('U') : 0;

            return $bTs <=> $aTs; // keep due_date desc inside each status group
        });

        return $list;
    }

    public function puedeMostrarAccionHecho(Usuario $viewer, array $perm, Task $task): bool
    {
        return $this->elActorPuedeCambiarEstadoTarea($viewer, $perm, $task);
    }

    public function CambiarEstadoTask(TaskCambiarEstadoRequest $d, ?Usuario $actor, array $perm = []): array
    {
        $task_id = $d->task_id;
        $status = (string) ($d->status ?? '');
        $nuevo = $this->normalizarStatus($status);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(Task::class)->find($task_id);
        if (!$entity instanceof Task) {
            return ['success' => false, 'error' => 'The requested record does not exist'];
        }
        if (null !== $actor) {
            if (!$this->elActorPuedeCambiarEstadoTarea($actor, $perm, $entity)) {
                return ['success' => false, 'error' => 'Not allowed to change this task state'];
            }
        }
        if ($entity->getStatus() === $nuevo) {
            return ['success' => true, 'message' => 'The operation was successful'];
        }
        $entity->setStatus($nuevo);
        $em->flush();
        $this->SalvarLog('Update', 'Task', 'The task status is changed: '.$nuevo);

        return ['success' => true, 'message' => 'The operation was successful'];
    }

    private function normalizarStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));
        if (Task::STATUS_COMPLETE === $s || 'completed' === $s) {
            return Task::STATUS_COMPLETE;
        }

        return Task::STATUS_PENDING;
    }

    private function parseDueDate(?string $due_day): ?\DateTimeInterface
    {
        if (null === $due_day || '' === $due_day) {
            return null;
        }
        $d = \DateTime::createFromFormat('m/d/Y', $due_day);

        return false !== $d ? $d : null;
    }

    public function elActorPuedeCambiarEstadoTarea(Usuario $actor, array $perm, Task $entity): bool
    {
        // Admin can always change status
        if ($actor->isAdministrador()) {
            return true;
        }
        
        // User can change status of their own assigned tasks (regardless of module access)
        $u = $entity->getAssignedUser();
        if (null !== $u && (int) $u->getUsuarioId() === (int) $actor->getUsuarioId()) {
            return true;
        }
        
        // For other tasks, need explicit edit permission on the module
        return !empty($perm['editar']);
    }

    private function resolverUsuario($usuario_id): ?Usuario
    {
        if (null === $usuario_id || '' === $usuario_id) {
            return null;
        }

        return $this->getDoctrine()->getRepository(Usuario::class)->find((int) $usuario_id);
    }
}
