<?php

namespace App\Repository;

use App\Entity\PermisoUsuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PermisoUsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermisoUsuario::class);
    }

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
            ->leftJoin('p_u.funcion', 'f')
            ->where('u.usuarioId = :usuario_id')
            ->setParameter('usuario_id', $usuario_id)
            ->orderBy('f.funcionId', 'ASC')
            ->getQuery();

        $lista = $consulta->getResult();
        return $lista;
    }

    /**
     * ListarPermisosFuncion: Lista los permisos de una funcion
     * @param int $funcion_id
     *
     * @return  PermisoUsuario[]
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