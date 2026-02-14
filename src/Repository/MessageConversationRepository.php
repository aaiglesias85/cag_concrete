<?php

namespace App\Repository;

use App\Entity\MessageConversation;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageConversation::class);
    }

    /**
     * Buscar o crear la conversación entre dos usuarios.
     * user1_id debe ser el menor y user2_id el mayor para unicidad.
     *
     * @param int $userIdA
     * @param int $userIdB
     * @return MessageConversation|null
     */
    public function BuscarConversacionEntreUsuarios(int $userIdA, int $userIdB): ?MessageConversation
    {
        $user1Id = min($userIdA, $userIdB);
        $user2Id = max($userIdA, $userIdB);

        return $this->createQueryBuilder('c')
            ->leftJoin('c.user1', 'u1')
            ->leftJoin('c.user2', 'u2')
            ->where('u1.usuarioId = :user1_id AND u2.usuarioId = :user2_id')
            ->setParameter('user1_id', $user1Id)
            ->setParameter('user2_id', $user2Id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Listar conversaciones de un usuario, ordenadas por última actividad.
     *
     * @param Usuario $user
     * @return MessageConversation[]
     */
    public function ListarConversacionesDeUsuario(Usuario $user): array
    {
        $userId = $user->getUsuarioId();
        if ($userId === null) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->leftJoin('c.user1', 'u1')
            ->leftJoin('c.user2', 'u2')
            ->where('u1.usuarioId = :user_id OR u2.usuarioId = :user_id')
            ->setParameter('user_id', $userId)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Comprueba si existe una conversación entre dos usuarios.
     *
     * @param int $userIdA
     * @param int $userIdB
     * @return bool
     */
    public function ExisteConversacionEntre(int $userIdA, int $userIdB): bool
    {
        return $this->BuscarConversacionEntreUsuarios($userIdA, $userIdB) !== null;
    }
}
