<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * ListarNotificationsDeUsuario: Lista las notificaciones de un usuario
     * @param int $usuario_id Id del usuario
     *
     * @return Notification[]
     */
    public function ListarNotificationsDeUsuario(int $usuario_id): array
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarNotificacionesDeProject: Lista las notificaciones de un proyecto
     *
     * @param int $project_id Id del proyecto
     * @return Notification[]
     */
    public function ListarNotificacionesDeProject(int $project_id): array
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.project', 'p');

        if ($project_id !== '') {
            $qb->andWhere('p.projectId = :project_id')
                ->setParameter('project_id', $project_id);
        }

        $qb->orderBy('n.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * ListarNotificationsDeUsuarioSinLeer: Lista las notificaciones no leídas de un usuario
     * @param int $usuario_id Id del usuario
     *
     * @return Notification[]
     */
    public function ListarNotificationsDeUsuarioSinLeer(int $usuario_id): array
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->andWhere('n.readed <> 1')
            ->setParameter('usuario_id', $usuario_id)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarNotifications: Lista las notificaciones con filtros, paginación y ordenación
     *
     * @return Notification[]
     */
    public function ListarNotifications(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0,
                                        ?string $fecha_inicial, ?string $fecha_fin, ?string $usuario_id = null): array
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u');

        // Agrupar condiciones de búsqueda
        if ($sSearch) {
            $qb->andWhere('u.nombre LIKE :search OR n.content LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($usuario_id) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        // Filtrar por fechas
        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00")->format("Y-m-d H:i:s");
            $qb->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59")->format("Y-m-d H:i:s");
            $qb->andWhere('n.createdAt <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        // Ordenación
        if ($iSortCol_0 === 'usuario') {
            $qb->orderBy("u.$iSortCol_0", $sSortDir_0);
        } else {
            $qb->orderBy("n.$iSortCol_0", $sSortDir_0);
        }

        // Paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->setFirstResult($start)
            ->getQuery()
            ->getResult();
    }

    /**
     * TotalNotifications: Devuelve el total de notificaciones según los filtros
     * @param string $sSearch Para buscar
     * @param string $fecha_inicial Fecha de inicio
     * @param string $fecha_fin Fecha de fin
     * @param string $usuario_id Id del usuario
     *
     * @return int
     */
    public function TotalNotifications(?string $sSearch, ?string $fecha_inicial, ?string $fecha_fin, ?string $usuario_id = null): int
    {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->leftJoin('n.usuario', 'u');

        // Agrupar condiciones de búsqueda
        if ($sSearch) {
            $qb->andWhere('u.nombre LIKE :search OR n.content LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($usuario_id) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        // Filtrar por fechas
        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00")->format("Y-m-d H:i:s");
            $qb->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59")->format("Y-m-d H:i:s");
            $qb->andWhere('n.createdAt <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarNotificacionesConTotal: Lista y cuenta notificaciones aplicando los mismos filtros.
     *
     */
    public function ListarNotificacionesConTotal(int $start, int $limit, ?string $sSearch = null, string $sortField = 'createdAt',
                                           string $sortDir = 'DESC', ?string $fecha_inicial = '', ?string $fecha_fin = '',
                                                 ?string $usuario_id = null, ?string $leida = ''): array {

        // Whitelist de campos ordenables
        $sortable = [
            'id' => 'n.id',
            'createdAt' => 'n.createdAt',
            'content' => 'n.content',
            'readed' => 'n.readed',
            'usuario' => 'u.nombre',   // map 'usuario' -> nombre del usuario
        ];
        $orderBy = $sortable[$sortField] ?? 'n.createdAt';
        $dir     = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        // QB base con JOIN y filtros
        $baseQb = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u');

        // Agrupar condiciones de búsqueda
        if ($sSearch) {
            $baseQb->andWhere('u.nombre LIKE :search OR n.content LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        if ($usuario_id) {
            $baseQb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        // Filtrar por fechas
        if ($fecha_inicial) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00")->format("Y-m-d H:i:s");
            $baseQb->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59")->format("Y-m-d H:i:s");
            $baseQb->andWhere('n.createdAt <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        if ($leida !== '') {
            $baseQb->andWhere('n.readed = :leida')
                ->setParameter('leida', $leida);
        }

        // ---- Datos (con paginación y orden) ----
        $dataQb = clone $baseQb;
        $dataQb->orderBy($orderBy, $dir)
            ->setFirstResult($start);

        if ($limit > 0) {
            $dataQb->setMaxResults($limit);
        }

        $data = $dataQb->getQuery()->getResult();

        // ---- Conteo filtrado (mismos filtros, sin orden/paginación) ----
        $countQb = clone $baseQb;
        $countQb->resetDQLPart('orderBy')
            ->select('COUNT(n.id)');

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data'  => $data,
            'total' => $total, // total con el MISMO filtro aplicado
        ];
    }

    /**
     * ListarNotificationsRangoFecha: Lista las notificaciones dentro de un rango de fechas
     * @param string $fecha_inicial Fecha de inicio
     * @param string $fecha_fin Fecha de fin
     * @param int $limit Limite de resultados
     * @param string $usuario_id Id del usuario
     * @param string $order Dirección de orden
     *
     * @return Notification[]
     */
    public function ListarNotificationsRangoFecha(?string $fecha_inicial, ?string $fecha_fin, ?int $limit = null, ?string $usuario_id = null, string $order = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u');

        if ($usuario_id) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if ($fecha_inicial) {
            $qb->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if ($fecha_fin) {
            $qb->andWhere('n.createdAt <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        $qb->orderBy('n.createdAt', $order);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
