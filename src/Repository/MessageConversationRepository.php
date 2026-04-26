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
     * Listar conversaciones donde el usuario participa (está en user1_id o en user2_id).
     * La tabla message_conversation tiene user1_id y user2_id: si el usuario actual coincide
     * con uno de los dos, es una conversación en la que participa y debe listarse en /chat.
     * Orden: última actividad (updated_at) descendente.
     *
     * @param Usuario $user Usuario autenticado
     *
     * @return MessageConversation[]
     */
    public function ListarConversacionesDeUsuario(Usuario $user): array
    {
        $userId = $user->getUsuarioId();
        if (null === $userId) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->where('IDENTITY(c.user1) = :user_id OR IDENTITY(c.user2) = :user_id')
            ->setParameter('user_id', $userId)
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Comprueba si existe una conversación entre dos usuarios.
     */
    public function ExisteConversacionEntre(int $userIdA, int $userIdB): bool
    {
        return null !== $this->BuscarConversacionEntreUsuarios($userIdA, $userIdB);
    }
}
