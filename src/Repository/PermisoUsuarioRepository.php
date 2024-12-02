<?php

namespace App\Repository;

use App\Entity\PermisoUsuario;
use Doctrine\ORM\EntityRepository;


class PermisoUsuarioRepository extends EntityRepository
{

    /**
     * BuscarPermisoUsuario: Devuelve el permiso usuario
     * @param int $usuario_id
     * @param int $funcion_id
     * @author Marcel
     */
    public function BuscarPermisoUsuario($usuario_id, $funcion_id)
    {
        $consulta = $this->createQueryBuilder('p_u')
            ->leftJoin('p_u.usuario', 'u')
            ->leftJoin('p_u.funcion', 'f')
            ->where('u.usuarioId = :usuario_id AND f.funcionId = :funcion_id')
            ->setParameter('usuario_id', $usuario_id)
            ->setParameter('funcion_id', $funcion_id)
            ->getQuery();

        $lista = $consulta->getOneOrNullResult();
        return $lista;
    }

    /**
     * ListarPermisosUsuario: Lista los permisos de un usuario
     * @param int $usuario_id
     *
     * @return PermisoUsuario[]
     */
    public function ListarPermisosUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('p_u')
            ->leftJoin('p_u.usuario', 'u')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id)
            ->getQuery();

        $lista = $consulta->getResult();
        return $lista;
    }

    /**
     * ListarPermisosFuncion: Lista los permisos de una funcion
     * @param int $funcion_id
     *
     * @author Marcel
     */
    public function ListarPermisosFuncion($funcion_id)
    {
        $consulta = $this->createQueryBuilder('p_u')
            ->leftJoin('p_u.funcion', 'f')
            ->where('f.funcionId = :funcion_id')
            ->setParameter('funcion_id', $funcion_id)
            ->getQuery();

        $lista = $consulta->getResult();
        return $lista;
    }
}