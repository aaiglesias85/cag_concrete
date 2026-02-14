<?php

namespace App\Utils\App;

use App\Entity\Message;
use App\Entity\MessageConversation;
use App\Entity\Usuario;
use App\Repository\MessageConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UsuarioRepository;
use App\Utils\Base;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Servicio de mensajería interna para la app.
 *
 * Proceso de traducción:
 * - Al enviar (EnviarMensaje): el cliente envía body + source_lang (es|en, idioma del remitente).
 *   Se guarda body_original y source_lang; se rellenan body_es y body_en. Si source_lang es 'es',
 *   body_es = original y body_en = traducir(original, es→en). Si es 'en', al revés.
 *   Si GOOGLE_TRANSLATE_API_KEY está configurada se usa Google Translate; si no, se devuelve el mismo texto.
 * - Al listar (ListarMensajes, ListarConversaciones): el parámetro $lang es el idioma del usuario
 *   que hace la petición (viene en la URL). Se devuelve body_es o body_en según ese $lang.
 *   El idioma del destinatario no se usa: cada usuario ve los mensajes en su idioma al abrir el chat
 *   porque su app envía su lang en la URL.
 */
class MessageService extends Base
{
    private MessageConversationRepository $conversationRepository;
    private MessageRepository $messageRepository;
    private UsuarioRepository $usuarioRepository;
    private EntityManagerInterface $em;
    private string $googleTranslateApiKey;

    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        \Symfony\Component\Mailer\MailerInterface $mailer,
        \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
        \Symfony\Bundle\SecurityBundle\Security $security,
        \Psr\Log\LoggerInterface $logger,
        MessageConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        UsuarioRepository $usuarioRepository,
        EntityManagerInterface $em,
        string $googleTranslateApiKey = ''
    ) {
        parent::__construct($container, $mailer, $containerBag, $security, $logger);
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->em = $em;
        $this->googleTranslateApiKey = $googleTranslateApiKey;
    }

    /**
     * Listar conversaciones del usuario autenticado (para lista de chats).
     * Estructura compatible con frontend: other_user, last_message, unread_count.
     *
     * @param string $lang Idioma del usuario (es|en) para devolver el texto del último mensaje en ese idioma
     * @return array{success: bool, conversations?: array, error?: string}
     */
    public function ListarConversaciones(string $lang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $userId = $usuario->getUsuarioId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        try {
            $conversations = $this->conversationRepository->ListarConversacionesDeUsuario($usuario);
            $list = [];
            foreach ($conversations as $conv) {
                $other = $conv->getOtherUser($usuario);
                if (!$other) {
                    continue;
                }
                $lastMessage = $this->messageRepository->ObtenerUltimoMensaje($conv);
                $unreadCount = $this->messageRepository->ContarNoLeidosEnConversacion($conv, $userId);
                $list[] = [
                    'conversation_id' => $conv->getConversationId(),
                    'other_user' => $this->formatearUsuarioCorto($other),
                    'last_message' => $lastMessage ? [
                        'text' => $lastMessage->getBodyForLang($lang) ?? $lastMessage->getBodyOriginal(),
                        'created_at' => $lastMessage->getCreatedAt() ? $lastMessage->getCreatedAt()->format('c') : null,
                    ] : null,
                    'unread_count' => $unreadCount,
                    'updated_at' => $conv->getUpdatedAt() ? $conv->getUpdatedAt()->format('c') : null,
                ];
            }
            return ['success' => true, 'conversations' => $list];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtener o crear conversación entre el usuario autenticado y otro usuario.
     *
     * @param int $otherUserId user_id del otro usuario
     * @param string $lang Idioma para mensajes
     * @return array{success: bool, conversation_id?: int, other_user?: array, error?: string}
     */
    public function ObtenerOcrearConversacion(int $otherUserId, string $lang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $userId = $usuario->getUsuarioId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }
        if ($otherUserId === $userId) {
            return ['success' => false, 'error' => 'No puede crear conversación consigo mismo'];
        }

        $otherUser = $this->usuarioRepository->find($otherUserId);
        if (!$otherUser instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario destinatario no encontrado'];
        }

        try {
            $conv = $this->conversationRepository->BuscarConversacionEntreUsuarios($userId, $otherUserId);
            if (!$conv) {
                $conv = new MessageConversation();
                $user1Id = min($userId, $otherUserId);
                $user2Id = max($userId, $otherUserId);
                $user1 = $this->usuarioRepository->find($user1Id);
                $user2 = $this->usuarioRepository->find($user2Id);
                $conv->setUser1($user1);
                $conv->setUser2($user2);
                $now = new \DateTime();
                $conv->setCreatedAt($now);
                $conv->setUpdatedAt($now);
                $this->em->persist($conv);
                $this->em->flush();
            }
            return [
                'success' => true,
                'conversation_id' => $conv->getConversationId(),
                'other_user' => $this->formatearUsuarioCorto($otherUser),
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Listar mensajes de una conversación (para pantalla de chat/inbox).
     *
     * @param int $conversationId
     * @param string $lang Idioma del usuario (es|en) para devolver body en ese idioma
     * @param int|null $limit
     * @param int $offset
     * @return array{success: bool, messages?: array, other_user?: array, error?: string}
     */
    public function ListarMensajes(int $conversationId, string $lang = 'es', ?int $limit = 100, int $offset = 0): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }

        $conv = $this->conversationRepository->find($conversationId);
        if (!$conv instanceof MessageConversation) {
            return ['success' => false, 'error' => 'Conversación no encontrada'];
        }
        $other = $conv->getOtherUser($usuario);
        if (!$other) {
            return ['success' => false, 'error' => 'No pertenece a esta conversación'];
        }

        $userId = $usuario->getUsuarioId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        try {
            // Al listar mensajes, marcar como leídos los que el usuario recibió (tiene sentido: ya los está viendo)
            $this->messageRepository->MarcarComoLeidos($conv, $userId);

            $messages = $this->messageRepository->ListarPorConversacion($conv, $limit, $offset);
            $list = [];
            foreach ($messages as $m) {
                $list[] = [
                    'message_id' => $m->getMessageId(),
                    'sender_id' => $m->getSender() ? $m->getSender()->getUsuarioId() : null,
                    'text' => $m->getBodyForLang($lang) ?? $m->getBodyOriginal(),
                    'created_at' => $m->getCreatedAt() ? $m->getCreatedAt()->format('c') : null,
                    'read_at' => $m->getReadAt() ? $m->getReadAt()->format('c') : null,
                ];
            }
            return [
                'success' => true,
                'messages' => $list,
                'other_user' => $this->formatearUsuarioCorto($other),
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar mensaje en una conversación. Rellena body_es y body_en (por ahora copia del original; se puede conectar Google Translate).
     *
     * @param int $conversationId
     * @param string $body Texto del mensaje
     * @param string $sourceLang es|en
     * @return array{success: bool, message?: array, error?: string}
     */
    public function EnviarMensaje(int $conversationId, string $body, string $sourceLang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }

        $conv = $this->conversationRepository->find($conversationId);
        if (!$conv instanceof MessageConversation) {
            return ['success' => false, 'error' => 'Conversación no encontrada'];
        }
        if (!$conv->getOtherUser($usuario)) {
            return ['success' => false, 'error' => 'No pertenece a esta conversación'];
        }

        $body = trim($body);
        if ($body === '') {
            return ['success' => false, 'error' => 'El mensaje no puede estar vacío'];
        }
        $sourceLang = $sourceLang === 'en' ? 'en' : 'es';

        try {
            $message = new Message();
            $message->setConversation($conv);
            $message->setSender($usuario);
            $message->setBodyOriginal($body);
            $message->setSourceLang($sourceLang);
            if ($sourceLang === 'es') {
                $message->setBodyEs($body);
                $message->setBodyEn($this->traducirTexto($body, 'es', 'en'));
            } else {
                $message->setBodyEn($body);
                $message->setBodyEs($this->traducirTexto($body, 'en', 'es'));
            }
            $now = new \DateTime();
            $message->setCreatedAt($now);
            $conv->setUpdatedAt($now);
            $this->em->persist($message);
            $this->em->flush();

            return [
                'success' => true,
                'message' => [
                    'message_id' => $message->getMessageId(),
                    'sender_id' => $usuario->getUsuarioId(),
                    'text' => $body,
                    'created_at' => $now->format('c'),
                    'read_at' => null,
                ],
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Listar usuarios con los que se puede iniciar chat (para selector "nuevo chat"). Excluye al usuario actual.
     *
     * @param string $search Búsqueda opcional por nombre/email
     * @return array{success: bool, users?: array, error?: string}
     */
    public function ListarUsuariosParaChat(string $search = ''): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $userId = $usuario->getUsuarioId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }
        try {
            $usuarios = $this->usuarioRepository->ListarOrdenados($search, '', '');
            $list = [];
            foreach ($usuarios as $u) {
                if ($u->getUsuarioId() === $userId) {
                    continue;
                }
                $list[] = $this->formatearUsuarioCorto($u);
            }
            return ['success' => true, 'users' => $list];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Marcar como leídos los mensajes de una conversación (para el usuario autenticado como destinatario).
     *
     * @param int $conversationId
     * @return array{success: bool, error?: string}
     */
    public function MarcarComoLeidos(int $conversationId): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $userId = $usuario->getUsuarioId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        $conv = $this->conversationRepository->find($conversationId);
        if (!$conv instanceof MessageConversation) {
            return ['success' => false, 'error' => 'Conversación no encontrada'];
        }
        if (!$conv->getOtherUser($usuario)) {
            return ['success' => false, 'error' => 'No pertenece a esta conversación'];
        }

        try {
            $this->messageRepository->MarcarComoLeidos($conv, $userId);
            return ['success' => true];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatearUsuarioCorto(Usuario $u): array
    {
        return [
            'user_id' => $u->getUsuarioId(),
            'name' => trim(($u->getNombre() ?? '') . ' ' . ($u->getApellidos() ?? '')),
            'image' => $u->getImagen() ?? '',
        ];
    }

    /**
     * Traduce texto entre es y en. Si no hay API configurada, devuelve el mismo texto.
     */
    private function traducirTexto(string $text, string $sourceLang, string $targetLang): string
    {
        if ($this->googleTranslateApiKey === '') {
            return $text;
        }
        try {
            if (!class_exists(\Google\Cloud\Translate\V2\TranslateClient::class)) {
                return $text;
            }
            $translate = new \Google\Cloud\Translate\V2\TranslateClient(['key' => $this->googleTranslateApiKey]);
            $result = $translate->translate($text, ['source' => $sourceLang, 'target' => $targetLang]);
            return $result['text'] ?? $text;
        } catch (\Throwable $e) {
            $this->logger->warning('Translation skipped: ' . $e->getMessage());
            return $text;
        }
    }
}
