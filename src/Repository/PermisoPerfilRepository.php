<?php

namespace App\Repository;

use App\Entity\PermisoPerfil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PermisoPerfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermisoPerfil::class);
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
}