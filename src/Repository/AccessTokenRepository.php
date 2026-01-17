<?php

namespace App\Repository;

use App\Entity\AccessToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    /**
     * ListarAccessTokenDeUsuario: Lista los access token de un usuario
     *
     * @param int $usuario_id Id del usuario
     * @return AccessToken[]
     */
    public function ListarAccessTokenDeUsuario(int $usuario_id): array
    {
        return $this->createQueryBuilder('a_t')
            ->leftJoin('a_t.user', 'u')
            ->where('u.usuarioId = :id')
            ->setParameter('id', $usuario_id)
            ->getQuery()
            ->getResult();
    }
}
