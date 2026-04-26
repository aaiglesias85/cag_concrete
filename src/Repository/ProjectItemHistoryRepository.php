<?php

namespace App\Repository;

use App\Entity\ProjectItemHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectItemHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectItemHistory::class);
    }

    /**
     * ListarHistorialDeItem: Lista el historial de cambios de un ProjectItem.
     *
     * @return ProjectItemHistory[]
     */
    public function ListarHistorialDeItem($project_item_id): array
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->leftJoin('h.user', 'u')
           ->where('p_i.id = :project_item_id')
           ->setParameter('project_item_id', $project_item_id)
           ->orderBy('h.createdAt', 'DESC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarHistorialDeUsuario: Lista el historial de cambios de un usuario.
     *
     * @return ProjectItemHistory[]
     */
    public function ListarHistorialDeUsuario($user_id): array
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.user', 'u')
           ->where('u.usuarioId = :user_id')
           ->setParameter('user_id', $user_id);

        return $consulta->getQuery()->getResult();
    }

    /**
     * TieneHistorialCantidad: Verifica si un ProjectItem tiene historial de cambios de cantidad.
     *
     * @param int $project_item_id
     */
    public function TieneHistorialCantidad($project_item_id): bool
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->where('p_i.id = :project_item_id')
           ->andWhere('h.actionType = :action_type')
           ->setParameter('project_item_id', $project_item_id)
           ->setParameter('action_type', 'update_quantity')
           ->setMaxResults(1);

        return count($consulta->getQuery()->getResult()) > 0;
    }

    /**
     * TieneHistorialPrecio: Verifica si un ProjectItem tiene historial de cambios de precio.
     *
     * @param int $project_item_id
     */
    public function TieneHistorialPrecio($project_item_id): bool
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->where('p_i.id = :project_item_id')
           ->andWhere('h.actionType = :action_type')
           ->setParameter('project_item_id', $project_item_id)
           ->setParameter('action_type', 'update_price')
           ->setMaxResults(1);

        return count($consulta->getQuery()->getResult()) > 0;
    }

    /**
     * TieneHistorialCantidadPorProyectoYItemId: Algún project_item del proyecto con ese item (catálogo) tiene historial de cantidad.
     */
    public function TieneHistorialCantidadPorProyectoYItemId(int $project_id, int $item_id): bool
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->leftJoin('p_i.project', 'p')
           ->leftJoin('p_i.item', 'it')
           ->where('p.projectId = :project_id')
           ->andWhere('it.itemId = :item_id')
           ->andWhere('h.actionType = :action_type')
           ->setParameter('project_id', $project_id)
           ->setParameter('item_id', $item_id)
           ->setParameter('action_type', 'update_quantity')
           ->setMaxResults(1);

        return count($consulta->getQuery()->getResult()) > 0;
    }

    /**
     * TieneHistorialPrecioPorProyectoYItemId: Algún project_item del proyecto con ese item tiene historial de precio.
     */
    public function TieneHistorialPrecioPorProyectoYItemId(int $project_id, int $item_id): bool
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->leftJoin('p_i.project', 'p')
           ->leftJoin('p_i.item', 'it')
           ->where('p.projectId = :project_id')
           ->andWhere('it.itemId = :item_id')
           ->andWhere('h.actionType = :action_type')
           ->setParameter('project_id', $project_id)
           ->setParameter('item_id', $item_id)
           ->setParameter('action_type', 'update_price')
           ->setMaxResults(1);

        return count($consulta->getQuery()->getResult()) > 0;
    }

    /**
     * TieneHistorialChangeOrderPorProyectoYItemId: Historial de alta de change order para ese item en el proyecto.
     */
    public function TieneHistorialChangeOrderPorProyectoYItemId(int $project_id, int $item_id): bool
    {
        $consulta = $this->createQueryBuilder('h')
           ->leftJoin('h.projectItem', 'p_i')
           ->leftJoin('p_i.project', 'p')
           ->leftJoin('p_i.item', 'it')
           ->where('p.projectId = :project_id')
           ->andWhere('it.itemId = :item_id')
           ->andWhere('h.actionType = :action_type')
           ->setParameter('project_id', $project_id)
           ->setParameter('item_id', $item_id)
           ->setParameter('action_type', 'add')
           ->setMaxResults(1);

        return count($consulta->getQuery()->getResult()) > 0;
    }
}
