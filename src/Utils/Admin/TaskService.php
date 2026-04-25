<?php

namespace App\Utils\Admin;

use App\Entity\Task;
use App\Entity\Usuario;
use App\Repository\TaskRepository;
use App\Utils\Base;

class TaskService extends Base
{
    public function CargarDatosTask($task_id): array
    {
        $resultado = [];
        $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
        if ($entity instanceof Task) {
            $u = $entity->getAssignedUser();
            $assignedLabel = '';
            if ($u !== null) {
                $assignedLabel = $u->getNombreCompleto() . '<' . ($u->getEmail() ?? '') . '>';
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

    public function EliminarTask($task_id): array
    {
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

    public function EliminarTasks($ids): array
    {
        $em = $this->getDoctrine()->getManager();
        $cant_eliminada = 0;
        $cant_total = 0;
        if ($ids !== '' && $ids !== null) {
            foreach (explode(',', (string) $ids) as $task_id) {
                if ($task_id === '') {
                    continue;
                }
                $cant_total++;
                $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
                if ($entity instanceof Task) {
                    $desc = mb_substr((string) $entity->getDescription(), 0, 80);
                    $em->remove($entity);
                    $cant_eliminada++;
                    $this->SalvarLog('Delete', 'Task', "The task is deleted: $desc");
                }
            }
        }
        $em->flush();

        if ($cant_eliminada === 0) {
            return ['success' => false, 'error' => 'No tasks could be deleted'];
        }

        $mensaje = ($cant_eliminada === $cant_total)
            ? 'The operation was successful'
            : 'The operation was successful. Some selected tasks could not be deleted';

        return ['success' => true, 'message' => $mensaje];
    }

    public function ActualizarTask($task_id, $description, $status, $due_day, $usuario_id): array
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository(Task::class)->find($task_id);
        if (!($entity instanceof Task)) {
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

    public function SalvarTask($description, $status, $due_day, $usuario_id): array
    {
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
    public function ListarTasks(
        $start,
        $limit,
        $sSearch,
        $orderField,
        $orderDir,
        $fecha_inicial,
        $fecha_fin,
        $statusFiltro,
        $usuarioFiltro,
    ): array {
        /** @var TaskRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Task::class);
        $resultado = $repo->ListarTasksConTotal(
            (int) $start,
            (int) $limit,
            $sSearch,
            (string) $orderField,
            (string) $orderDir,
            (string) $fecha_inicial,
            (string) $fecha_fin,
            (string) $statusFiltro,
            (string) $usuarioFiltro,
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
        if ($p === 'all' || $p === 'all_time' || $p === '') {
            return ['inicial' => '', 'final' => ''];
        }
        if ($p === 'custom') {
            return [
                'inicial' => trim($customFi),
                'final' => trim($customFf),
            ];
        }
        $now = new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        if ($p === 'current_month') {
            $inicio = (clone $now)->modify('first day of this month')->setTime(0, 0, 0);
            $fin = (clone $now)->modify('last day of this month')->setTime(0, 0, 0);

            return [
                'inicial' => $inicio->format('m/d/Y'),
                'final' => $fin->format('m/d/Y'),
            ];
        }
        if ($p === 'last_month') {
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
     * Tasks for the home widget: admin sees all; other users only rows assigned to them.
     *
     * @return list<array<string, mixed>>
     */
    public function listarTareasPayloadHome(Usuario $viewer, array $perm, string $fi, string $ff, int $limit = 30): array
    {
        if (empty($perm['ver'])) {
            return [];
        }
        $only = $viewer->isAdministrador() ? null : (int) $viewer->getUsuarioId();

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
                'can_mark_done' => $this->puedeMostrarAccionHecho($viewer, $perm, $task),
            ];
        }

        return $list;
    }

    public function puedeMostrarAccionHecho(Usuario $viewer, array $perm, Task $task): bool
    {
        if ($this->normalizarStatus($task->getStatus()) === Task::STATUS_COMPLETE) {
            return false;
        }

        return $this->elActorPuedeCambiarEstadoTarea($viewer, $perm, $task);
    }

    private function normalizarStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));
        if ($s === Task::STATUS_COMPLETE || $s === 'completed') {
            return Task::STATUS_COMPLETE;
        }

        return Task::STATUS_PENDING;
    }

    private function parseDueDate(?string $due_day): ?\DateTimeInterface
    {
        if ($due_day === null || $due_day === '') {
            return null;
        }
        $d = \DateTime::createFromFormat('m/d/Y', $due_day);

        return $d !== false ? $d : null;
    }

   public function elActorPuedeCambiarEstadoTarea(Usuario $actor, array $perm, Task $entity): bool
   {
      if (empty($perm['editar'])) {
         return false;
      }
      if ($actor->isAdministrador()) {
         return true;
      }
      $u = $entity->getAssignedUser();

      return $u !== null && (int) $u->getUsuarioId() === (int) $actor->getUsuarioId();
   }

   public function CambiarEstadoTask($task_id, string $status, ?Usuario $actor, array $perm = []): array
   {
      $nuevo = $this->normalizarStatus($status);
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository(Task::class)->find($task_id);
      if (!($entity instanceof Task)) {
         return ['success' => false, 'error' => 'The requested record does not exist'];
      }
      if ($actor !== null) {
         if (!$this->elActorPuedeCambiarEstadoTarea($actor, $perm, $entity)) {
            return ['success' => false, 'error' => 'Not allowed to change this task state'];
         }
      }
      if ($entity->getStatus() === $nuevo) {
         return ['success' => true, 'message' => 'The operation was successful'];
      }
      $entity->setStatus($nuevo);
      $em->flush();
      $this->SalvarLog('Update', 'Task', 'The task status is changed: ' . $nuevo);

      return ['success' => true, 'message' => 'The operation was successful'];
   }

   private function resolverUsuario($usuario_id): ?Usuario
   {
      if ($usuario_id === null || $usuario_id === '') {
         return null;
      }

      return $this->getDoctrine()->getRepository(Usuario::class)->find((int) $usuario_id);
   }
}
