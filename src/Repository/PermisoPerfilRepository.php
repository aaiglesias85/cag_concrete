<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;


class PermisoPerfilRepository extends EntityRepository
{

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
}