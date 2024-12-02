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
    public function ListarLogsDeUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id);

        $consulta->addOrderBy('l.fecha', 'DESC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarLogs: Lista los logs
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Log[]
     */
    public function ListarLogs($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id = "")
    {
        $consulta = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u');


        if ($sSearch != "") {
            $consulta->andWhere('u.nombre LIKE :nombre OR l.operacion LIKE :operacion OR l.categoria LIKE :categoria OR l.descripcion LIKE :descripcion OR l.ip LIKE :ip')
                ->setParameter('nombre', "%${sSearch}%")
                ->setParameter('operacion', "%${sSearch}%")
                ->setParameter('categoria', "%${sSearch}%")
                ->setParameter('descripcion', "%${sSearch}%")
                ->setParameter('ip', "%${sSearch}%");
        }

        if ($usuario_id != "") {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('l.fecha >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('l.fecha <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($iSortCol_0 != "nombre") {
            $consulta->orderBy("l.$iSortCol_0", $sSortDir_0);
        }
        if ($iSortCol_0 == "nombre") {
            $consulta->orderBy("u.$iSortCol_0", $sSortDir_0);
        }

        if ($limit > 0) {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->setFirstResult($start)
            ->getQuery()->getResult();
        return $lista;
    }

    /**
     * TotalLogs: Total de tareas de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalLogs($sSearch, $fecha_inicial, $fecha_fin, $usuario_id = "")
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(l.logId) FROM App\Entity\Log l ';
        $join = ' LEFT JOIN l.usuario u ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (u.nombre LIKE :nombre OR l.operacion LIKE :operacion OR l.categoria LIKE :categoria OR l.descripcion LIKE :descripcion  OR l.ip LIKE :ip) ';
            } else {
                $where .= 'AND (u.nombre LIKE :nombre OR l.operacion LIKE :operacion OR l.categoria LIKE :categoria OR l.descripcion LIKE :descripcion  OR l.ip LIKE :ip) ';
            }
        }

        if ($usuario_id != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (u.usuarioId = :usuario_id) ';
            } else {
                $where .= 'AND (u.usuarioId = :usuario_id) ';
            }
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE l.fecha >= :inicio ';
            } else {
                $where .= ' AND l.fecha >= :inicio ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE l.fecha <= :fin ';
            } else {
                $where .= ' AND l.fecha <= :fin ';
            }
        }

        $consulta .= $join;
        $consulta .= $where;
        $query = $em->createQuery($consulta);
        //Adicionar parametros        
        //$sSearch  
        $esta_query_usuario_id = substr_count($consulta, ':usuario_id');
        if ($esta_query_usuario_id == 1) {
            $query->setParameter('usuario_id', $usuario_id);
        }

        $esta_query_inicio = substr_count($consulta, ':inicio');
        if ($esta_query_inicio == 1) {
            $query->setParameter('inicio', $fecha_inicial);
        }

        $esta_query_fin = substr_count($consulta, ':fin');
        if ($esta_query_fin == 1) {
            $query->setParameter('fin', $fecha_fin);
        }

        $esta_query_nombre = substr_count($consulta, ':nombre');
        if ($esta_query_nombre == 1) {
            $query->setParameter('nombre', "%${sSearch}%");
        }

        $esta_query_operacion = substr_count($consulta, ':operacion');
        if ($esta_query_operacion == 1) {
            $query->setParameter('operacion', "%${sSearch}%");
        }

        $esta_query_categoria = substr_count($consulta, ':categoria');
        if ($esta_query_categoria == 1) {
            $query->setParameter('categoria', "%${sSearch}%");
        }

        $esta_query_descripcion = substr_count($consulta, ':descripcion');
        if ($esta_query_descripcion == 1) {
            $query->setParameter('descripcion', "%${sSearch}%");
        }

        $esta_query_ip = substr_count($consulta, ':ip');
        if ($esta_query_ip == 1) {
            $query->setParameter('ip', "%${sSearch}%");
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

    /**
     * ListarLogsRangoFecha: Lista los logs por un rango de fecha
     *
     * @param int $start Inicio
     * @param int $limit Limite
     *
     * @return Log[]
     */
    public function ListarLogsRangoFecha($fecha_inicial, $fecha_fin, $limit = "", $usuario_id = "", $order = 'ASC')
    {
        $consulta = $this->createQueryBuilder('l')
            ->leftJoin('l.usuario', 'u');

        if ($usuario_id != "") {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }


        if ($fecha_inicial != "") {
            $consulta->andWhere('l.fecha >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $consulta->andWhere('l.fecha <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('l.fecha', $order);

        if ($limit != "") {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->getQuery()->getResult();
        return $lista;
    }

}
