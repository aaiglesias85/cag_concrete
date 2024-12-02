<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;


class PermisoPerfilRepository extends EntityRepository
{

    /**
     * BuscarPermisoPerfil: Devuelve el permiso perfil
     * @param int $perfil_id
     * @param int $funcion_id
     * @author Marcel
     */
    public function BuscarPermisoPerfil($perfil_id, $funcion_id)
    {
        $consulta = $this->createQueryBuilder('p_p')
            ->leftJoin('p_p.perfil', 'p')
            ->leftJoin('p_p.funcion', 'f')
            ->where('p.rolId = :perfil_id AND f.funcionId = :funcion_id')
            ->setParameter('perfil_id', $perfil_id)
            ->setParameter('funcion_id', $funcion_id)
            ->getQuery();

        $lista = $consulta->getOneOrNullResult();
        return $lista;
    }

    /**
     * ListarPermisosPerfil: Lista los permisos de un perfil
     * @param int $perfil_id
     *
     * @author Marcel
     */
    public function ListarPermisosPerfil($perfil_id)
    {
        $consulta = $this->createQueryBuilder('p_p')
            ->leftJoin('p_p.perfil', 'p')
            ->where('p.rolId = :perfil_id')
            ->setParameter('perfil_id', $perfil_id)
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
        $consulta = $this->createQueryBuilder('p_p')
            ->leftJoin('p_p.funcion', 'f')
            ->where('f.funcionId = :funcion_id')
            ->setParameter('funcion_id', $funcion_id)
            ->getQuery();

        $lista = $consulta->getResult();
        return $lista;
    }
}