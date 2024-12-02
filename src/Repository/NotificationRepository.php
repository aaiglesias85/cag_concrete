<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{

    /**
     * ListarNotificationsDeUsuario: Lista las notifications de un usuario de la BD
     * @param int $usuario_id Id del usuario
     *
     * @return Notification[]
     */
    public function ListarNotificationsDeUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id);

        $consulta->addOrderBy('n.createdAt', 'DESC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarNotificationsDeUsuarioSinLeer: Lista las notifications de un usuario sin leer de la BD
     * @param int $usuario_id Id del usuario
     *
     * @return Notification[]
     */
    public function ListarNotificationsDeUsuarioSinLeer($usuario_id)
    {
        $consulta = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->andWhere('n.readed <> 1')
            ->setParameter('usuario_id', $usuario_id);

        $consulta->addOrderBy('n.createdAt', 'DESC');

        return $consulta->getQuery()->getResult();
    }

    /**
     * ListarNotifications: Lista los notifications
     * @param int $start Inicio
     * @param int $limit Limite
     * @param string $sSearch Para buscar
     *
     * @return Notification[]
     */
    public function ListarNotifications($start, $limit, $sSearch, $iSortCol_0, $sSortDir_0, $fecha_inicial, $fecha_fin, $usuario_id = "")
    {
        $consulta = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u');


        if ($sSearch != "") {
            $consulta->andWhere('u.nombre LIKE :nombre OR n.content LIKE :content')
                ->setParameter('nombre', "%${sSearch}%")
                ->setParameter('content', "%${sSearch}%")
                ;
        }

        if ($usuario_id != "") {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        if ($fecha_inicial != "") {

            $fecha_inicial = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_inicial . " 00:00:00");
            $fecha_inicial = $fecha_inicial->format("Y-m-d H:i:s");

            $consulta->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $consulta->andWhere('n.createdAt <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        if ($iSortCol_0 != "usuario") {
            $consulta->orderBy("n.$iSortCol_0", $sSortDir_0);
        }
        if ($iSortCol_0 == "usuario") {
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
     * TotalNotifications: Total de tareas de la BD
     * @param string $sSearch Para buscar
     *
     * @author Marcel
     */
    public function TotalNotifications($sSearch, $fecha_inicial, $fecha_fin, $usuario_id = "")
    {
        $em = $this->getEntityManager();
        $consulta = 'SELECT COUNT(n.id) FROM App\Entity\Notification n ';
        $join = ' LEFT JOIN n.usuario u ';
        $where = '';

        if ($sSearch != "") {
            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (u.nombre LIKE :nombre OR n.content LIKE :content) ';
            } else {
                $where .= 'AND (u.nombre LIKE :nombre OR n.content LIKE :content) ';
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
                $where .= 'WHERE (n.createdAt >= :inicio) ';
            } else {
                $where .= ' AND (n.createdAt >= :inicio) ';
            }
        }

        if ($fecha_fin != "") {

            $fecha_fin = \DateTime::createFromFormat("m/d/Y H:i:s", $fecha_fin . " 23:59:59");
            $fecha_fin = $fecha_fin->format("Y-m-d H:i:s");

            $esta_query = explode("WHERE", $where);
            if (count($esta_query) == 1) {
                $where .= 'WHERE (n.createdAt <= :fin) ';
            } else {
                $where .= ' AND (n.createdAt <= :fin) ';
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

        $esta_query_content = substr_count($consulta, ':content');
        if ($esta_query_content == 1) {
            $query->setParameter('content', "%${sSearch}%");
        }

        $total = $query->getSingleScalarResult();
        return $total;
    }

    /**
     * ListarNotificationsRangoFecha: Lista los notifications por un rango de fecha
     *
     * @param int $start Inicio
     * @param int $limit Limite
     *
     * @return Notification[]
     */
    public function ListarNotificationsRangoFecha($fecha_inicial, $fecha_fin, $limit = "", $usuario_id = "", $order = 'ASC')
    {
        $consulta = $this->createQueryBuilder('n')
            ->leftJoin('n.usuario', 'u');

        if ($usuario_id != "") {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }


        if ($fecha_inicial != "") {
            $consulta->andWhere('n.createdAt >= :fecha_inicial')
                ->setParameter('fecha_inicial', $fecha_inicial);
        }
        if ($fecha_fin != "") {

            $consulta->andWhere('n.createdAt <= :fecha_final')
                ->setParameter('fecha_final', $fecha_fin);
        }

        $consulta->orderBy('n.createdAt', $order);

        if ($limit != "") {
            $consulta->setMaxResults($limit);
        }

        $lista = $consulta->getQuery()->getResult();
        return $lista;
    }

}
