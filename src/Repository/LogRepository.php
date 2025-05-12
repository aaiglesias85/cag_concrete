<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository
{
    /**
     * ListarLogsDeUsuario: Lista los logs de un usuario de la BD
     * @param int $usuario_id Id del usuario
     *
     * @return Log[]
     */
    public function ListarLogsDeUsuario(int $usuario_id): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id)
            ->addOrderBy('l.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * ListarLogs: Lista los logs con paginación y filtros
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     * @param string $iSortCol_0 Columna para ordenar
     * @param string $sSortDir_0 Dirección de ordenamiento
     * @param string $fecha_inicial Fecha inicial
     * @param string $fecha_fin Fecha final
     * @param string $usuario_id Id del usuario
     *
     * @return Log[]
     */
    public function ListarLogs(int $start, int $limit, ?string $sSearch, string $iSortCol_0, string $sSortDir_0, ?string $fecha_inicial, ?string $fecha_fin, ?string $usuario_id): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u');

        // Agrupar el WHERE de búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('u.nombre LIKE :search OR l.operacion LIKE :search OR l.categoria LIKE :search OR l.descripcion LIKE :search OR l.ip LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtros adicionales
        if (!empty($usuario_id)) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if (!empty($fecha_inicial)) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00")->format("Y-m-d H:i:s");
            $qb->andWhere('l.fecha >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if (!empty($fecha_fin)) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59")->format("Y-m-d H:i:s");
            $qb->andWhere('l.fecha <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        // Ordenar resultados
        if ($iSortCol_0 === 'nombre') {
            $qb->orderBy('u.' . $iSortCol_0, $sSortDir_0);
        } else {
            $qb->orderBy('l.' . $iSortCol_0, $sSortDir_0);
        }

        // Paginación
        if ($limit > 0) {
            $qb->setMaxResults($limit)
                ->setFirstResult($start);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * TotalLogs: Devuelve el total de logs con filtros aplicados
     * @param string $sSearch Para buscar
     * @param string $fecha_inicial Fecha inicial
     * @param string $fecha_fin Fecha final
     * @param string $usuario_id Id del usuario
     *
     * @return int
     */
    public function TotalLogs(?string $sSearch, ?string $fecha_inicial, ?string $fecha_fin, ?string $usuario_id): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.logId)')
            ->leftJoin('l.usuario', 'u');

        // Agrupar el WHERE de búsqueda
        if (!empty($sSearch)) {
            $qb->andWhere('u.nombre LIKE :search OR l.operacion LIKE :search OR l.categoria LIKE :search OR l.descripcion LIKE :search OR l.ip LIKE :search')
                ->setParameter('search', "%{$sSearch}%");
        }

        // Filtros adicionales
        if (!empty($usuario_id)) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if (!empty($fecha_inicial)) {
            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00")->format("Y-m-d H:i:s");
            $qb->andWhere('l.fecha >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if (!empty($fecha_fin)) {
            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59")->format("Y-m-d H:i:s");
            $qb->andWhere('l.fecha <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * ListarLogsRangoFecha: Lista los logs por un rango de fecha
     *
     * @param string $fecha_inicial Fecha inicial
     * @param string $fecha_fin Fecha final
     * @param int $limit Limite
     * @param string $usuario_id Id del usuario
     * @param string $order Dirección de orden
     *
     * @return Log[]
     */
    public function ListarLogsRangoFecha(?string $fecha_inicial, ?string $fecha_fin, ?int $limit, ?string $usuario_id, string $order = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u');

        // Filtros de fecha y usuario
        if (!empty($usuario_id)) {
            $qb->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if (!empty($fecha_inicial)) {
            $qb->andWhere('l.fecha >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }

        if (!empty($fecha_fin)) {
            $qb->andWhere('l.fecha <= :fecha_fin')
                ->setParameter('fecha_fin', $fecha_fin);
        }

        $qb->orderBy('l.fecha', $order);

        // Limitar resultados
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}