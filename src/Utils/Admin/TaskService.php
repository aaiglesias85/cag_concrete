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

   public function CambiarEstadoTask($task_id, string $status): array
   {
      $nuevo = $this->normalizarStatus($status);
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository(Task::class)->find($task_id);
      if (!($entity instanceof Task)) {
         return ['success' => false, 'error' => 'The requested record does not exist'];
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
