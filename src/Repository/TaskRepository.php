<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return array{data: Task[], total: int}
     * @param int|null $onlyAssignedToUserId if set, only tasks with assignee with this user id
     */
    public function ListarTasksConTotal(
        int $start,
        int $limit,
        ?string $sSearch = null,
        string $sortField = 'due_date',
        string $sortDir = 'desc',
        ?string $fecha_inicial = '',
        ?string $fecha_fin = '',
        ?string $statusFiltro = '',
        ?string $usuarioFiltro = '',
        ?int $onlyAssignedToUserId = null,
    ): array {
        $sortable = [
            'id' => 't.taskId',
            'description' => 't.description',
            'due_date' => 't.dueDate',
            'status' => 't.status',
            'created_at' => 't.createdAt',
            'assigned' => 'u.nombre',
        ];
        $orderBy = $sortable[$sortField] ?? 't.dueDate';
        $dir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        $baseQb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignedUser', 'u');

        if ($sSearch !== null && $sSearch !== '') {
            $baseQb->andWhere('t.description LIKE :search OR u.nombre LIKE :search OR u.apellidos LIKE :search OR u.email LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($fecha_inicial !== null && $fecha_inicial !== '') {
            $fi = \DateTime::createFromFormat('m/d/Y', $fecha_inicial);
            if ($fi !== false) {
                $baseQb->andWhere('t.dueDate IS NOT NULL AND t.dueDate >= :fecha_inicial')
                    ->setParameter('fecha_inicial', $fi->format('Y-m-d'));
            }
        }

        if ($fecha_fin !== null && $fecha_fin !== '') {
            $ff = \DateTime::createFromFormat('m/d/Y', $fecha_fin);
            if ($ff !== false) {
                $baseQb->andWhere('t.dueDate IS NOT NULL AND t.dueDate <= :fecha_final')
                    ->setParameter('fecha_final', $ff->format('Y-m-d'));
            }
        }

        if ($statusFiltro !== null && $statusFiltro !== '') {
            $baseQb->andWhere('t.status = :st')
                ->setParameter('st', $statusFiltro);
        }

        if ($usuarioFiltro !== null && $usuarioFiltro !== '') {
            $baseQb->andWhere('u.usuarioId = :uid')
                ->setParameter('uid', (int) $usuarioFiltro);
        }

        if ($onlyAssignedToUserId !== null) {
            $baseQb->andWhere('u.usuarioId = :onlyUid')
                ->setParameter('onlyUid', (int) $onlyAssignedToUserId);
        }

        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir);
        if ($sortField === 'assigned') {
            $dataQb->addOrderBy('u.apellidos', $dir);
        }
        $dataQb->setFirstResult($start);
        if ($limit > 0) {
            $dataQb->setMaxResults($limit);
        }
        $data = $dataQb->getQuery()->getResult();

        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')->select('COUNT(t.taskId)');
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return ['data' => $data, 'total' => $total];
    }
}
