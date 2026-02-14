<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\MessageConversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Listar mensajes de una conversación, ordenados por fecha (más recientes al final).
     *
     * @param MessageConversation $conversation
     * @param int|null $limit
     * @param int $offset
     * @return Message[]
     */
    public function ListarPorConversacion(MessageConversation $conversation, ?int $limit = null, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'ASC')
            ->setFirstResult($offset);

        if ($limit !== null && $limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Contar mensajes no leídos en una conversación para un usuario (mensajes que no envió y read_at es null).
     *
     * @param MessageConversation $conversation
     * @param int $userId Id del usuario que recibe (destinatario)
     * @return int
     */
    public function ContarNoLeidosEnConversacion(MessageConversation $conversation, int $userId): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.messageId)')
            ->leftJoin('m.sender', 's')
            ->where('m.conversation = :conversation')
            ->andWhere('s.usuarioId != :user_id')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('conversation', $conversation)
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marcar como leídos todos los mensajes de una conversación recibidos por el usuario dado
     * (mensajes que no envió el usuario: sender_id != userId).
     *
     * @param MessageConversation $conversation
     * @param int $userId Id del usuario que lee (destinatario)
     */
    public function MarcarComoLeidos(MessageConversation $conversation, int $userId): void
    {
        $em = $this->getEntityManager();
        $now = new \DateTime();

        $messages = $this->createQueryBuilder('m')
            ->innerJoin('m.sender', 's')
            ->where('m.conversation = :conversation')
            ->andWhere('s.usuarioId != :user_id')
            ->andWhere('m.readAt IS NULL')
            ->setParameter('conversation', $conversation)
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getResult();

        foreach ($messages as $message) {
            $message->setReadAt($now);
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * Obtener el último mensaje de una conversación (para vista de lista de chats).
     *
     * @param MessageConversation $conversation
     * @return Message|null
     */
    public function ObtenerUltimoMensaje(MessageConversation $conversation): ?Message
    {
        return $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
