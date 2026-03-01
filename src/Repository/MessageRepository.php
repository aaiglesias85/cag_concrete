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

    /**
     * Obtener el último mensaje visible para un usuario (excluye los eliminados "para mí").
     *
     * @param MessageConversation $conversation
     * @param int $userId
     * @return Message|null
     */
    public function ObtenerUltimoMensajeVisibleParaUsuario(MessageConversation $conversation, int $userId): ?Message
    {
        $messages = $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
        foreach ($messages as $m) {
            if (!$m->isDeletedForUser($userId)) {
                return $m;
            }
        }
        return null;
    }

    /**
     * Marcar todos los mensajes de una conversación como "eliminados para mí" por un usuario.
     * Se usa al eliminar el chat: borra el historial para ese usuario.
     *
     * @param MessageConversation $conversation
     * @param int $userId
     */
    public function MarcarTodosComoEliminadosParaUsuario(MessageConversation $conversation, int $userId): void
    {
        $messages = $this->createQueryBuilder('m')
            ->where('m.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getResult();

        $em = $this->getEntityManager();
        foreach ($messages as $message) {
            if (!$message->isDeletedForUser($userId)) {
                $message->addDeletedForUser($userId);
                $em->persist($message);
            }
        }
        $em->flush();
    }

    /**
     * Suma de caracteres de body_original de mensajes creados entre fecha inicio y fecha fin (mes en curso: primer día del mes hasta la fecha actual).
     * Sirve para comprobar el límite free de Google Translate (500k caracteres/mes).
     *
     * @param \DateTimeInterface $start Fecha inicial (ej. primer día del mes en curso 00:00:00)
     * @param \DateTimeInterface $end Fecha final (ej. fecha actual) o null para no acotar por el final
     * @return int
     */
    public function sumBodyOriginalLengthForMonth(\DateTimeInterface $start, ?\DateTimeInterface $end = null): int
    {
        $conn = $this->getEntityManager()->getConnection();
        if ($end !== null) {
            $sql = 'SELECT COALESCE(SUM(CHAR_LENGTH(body_original)), 0) FROM message WHERE created_at >= :start AND created_at <= :end';
            $result = $conn->executeQuery($sql, [
                'start' => $start->format('Y-m-d H:i:s'),
                'end'   => $end->format('Y-m-d H:i:s'),
            ]);
        } else {
            $sql = 'SELECT COALESCE(SUM(CHAR_LENGTH(body_original)), 0) FROM message WHERE created_at >= :start';
            $result = $conn->executeQuery($sql, ['start' => $start->format('Y-m-d H:i:s')]);
        }
        return (int) $result->fetchOne();
    }

    /**
     * Suma de caracteres (body_original) de mensajes traducidos a petición en el periodo (para límite 500k/mes).
     *
     * @param \DateTimeInterface $start
     * @param \DateTimeInterface $end
     * @return int
     */
    public function sumTranslatedCharactersForPeriod(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT COALESCE(SUM(CHAR_LENGTH(body_original)), 0) FROM message WHERE translated_at >= :start AND translated_at <= :end';
        $result = $conn->executeQuery($sql, [
            'start' => $start->format('Y-m-d H:i:s'),
            'end'   => $end->format('Y-m-d H:i:s'),
        ]);
        return (int) $result->fetchOne();
    }
}
