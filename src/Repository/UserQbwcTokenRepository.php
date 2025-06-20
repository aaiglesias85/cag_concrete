<?php

namespace App\Repository;

use App\Entity\UserQbwcToken;
use Doctrine\ORM\EntityRepository;

class UserQbwcTokenRepository extends EntityRepository
{

    /**
     * BuscarToken: Buscar un token
     *
     * @return UserQbwcToken
     */
    public function BuscarToken($token)
    {
        $consulta = $this->createQueryBuilder('u_q_t');

        if ($token != '') {
            $consulta->andWhere('u_q_t.token = :token')
                ->setParameter('token', $token);
        }

        return $consulta->getQuery()->getOneOrNullResult();
    }

    /**
     * ListarTokensDeUsuario: Lista los tokens de un usuario
     *
     * @return UserQbwcToken[]
     */
    public function ListarTokensDeUsuario($usuario_id)
    {
        $consulta = $this->createQueryBuilder('u_q_t')
            ->leftJoin('u_q_t.usuario', 'u');

        if ($usuario_id != '') {
            $consulta->andWhere('u.usuarioId = :usuario_id')
                ->setParameter('usuario_id', $usuario_id);
        }

        $consulta->orderBy('u_q_t.id', "DESC");

        return $consulta->getQuery()->getResult();
    }
}
