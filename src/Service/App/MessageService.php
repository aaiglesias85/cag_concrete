<?php

namespace App\Service\App;

use App\Entity\Message;
use App\Entity\MessageConversation;
use App\Entity\Usuario;
use App\Repository\MessageConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UsuarioRepository;
use App\Service\Admin\WidgetAccessService;
use App\Service\Base;
use App\Service\PushNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Servicio de mensajería interna para la app.
 *
 * Traducción: ya no se traduce al enviar. Se guarda el texto original en body_es y body_en.
 * Si el usuario no entiende un mensaje, puede pulsar "Traducir" en la app; la app llama al
 * endpoint POST /message/traducir y se traduce solo entonces (límite 500k caracteres/mes por uso).
 */
class MessageService extends Base
{
    private MessageConversationRepository $conversationRepository;
    private MessageRepository $messageRepository;
    private UsuarioRepository $usuarioRepository;
    private EntityManagerInterface $em;
    private string $googleTranslateApiKey;
    private PushNotificationService $pushNotificationService;
    private HttpClientInterface $httpClient;
    private string $projectDir;

    private const TRANSLATE_LIMIT_CHARS_PER_MONTH = 500_000;
    /** Segundos para permitir "eliminar para todos" (1 hora, como WhatsApp). */
    private const DELETE_FOR_EVERYONE_WINDOW_SECONDS = 3600;
    private const TRANSLATIONS_EN = 'translations/messages+intl-icu.en.yaml';
    private const TRANSLATIONS_ES = 'translations/messages+intl-icu.es.yaml';
    private const GOOGLE_TRANSLATE_API_URL = 'https://translation.googleapis.com/language/translate/v2';

    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        WidgetAccessService $widgetAccessService,
        MessageConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        UsuarioRepository $usuarioRepository,
        EntityManagerInterface $em,
        PushNotificationService $pushNotificationService,
        HttpClientInterface $httpClient,
        string $googleTranslateApiKey = '',
        string $projectDir = '',
    ) {
        parent::__construct($doctrine, $mailer, $containerBag, $security, $logger, $urlGenerator, $twig, $widgetAccessService);
        $this->conversationRepository = $conversationRepository;
        $this->messageRepository = $messageRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->em = $em;
        $this->pushNotificationService = $pushNotificationService;
        $this->httpClient = $httpClient;
        $this->googleTranslateApiKey = $googleTranslateApiKey;
        $this->projectDir = $projectDir;
    }

    /**
     * Listar conversaciones del usuario autenticado (para lista de chats).
     * Estructura compatible con frontend: other_user, last_message, unread_count.
     *
     * @return array{success: bool, conversations?: array, error?: string}
     */
    /**
     * Comprueba si el usuario tiene permiso de chat. Si no, devuelve array de error para 403.
     *
     * @return array{success: false, error: 'chat_forbidden'}|null null si tiene permiso
     */
    private function checkPermisoChat(Usuario $usuario): ?array
    {
        if (!$usuario->getChat()) {
            return ['success' => false, 'error' => 'chat_forbidden'];
        }

        return null;
    }

    public function ListarConversaciones(string $lang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        try {
            $conversations = $this->conversationRepository->ListarConversacionesDeUsuario($usuario);
            $list = [];
            foreach ($conversations as $conv) {
                if ($conv->isHiddenForUser($userId)) {
                    continue;
                }
                $other = $conv->getOtherUser($usuario);
                if (!$other) {
                    continue;
                }
                $lastMessage = $this->messageRepository->ObtenerUltimoMensajeVisibleParaUsuario($conv, $userId);
                $unreadCount = $this->messageRepository->ContarNoLeidosEnConversacion($conv, $userId);
                $lastText = null;
                if ($lastMessage) {
                    $lastSenderId = $lastMessage->getSender() ? $lastMessage->getSender()->getUsuarioId() : null;
                    $lastIsOwn = $lastSenderId === $userId;
                    if (null !== $lastMessage->getDeletedForEveryoneAt()) {
                        $lastText = 'message_deleted_placeholder';
                    } else {
                        $lastText = $lastIsOwn
                           ? ($lastMessage->getBodyOriginal() ?? $lastMessage->getBodyForLang($lang))
                           : ($lastMessage->getBodyForLang($lang) ?? $lastMessage->getBodyOriginal());
                    }
                }
                $list[] = [
                    'conversation_id' => $conv->getConversationId(),
                    'other_user' => $this->formatearUsuarioCorto($other),
                    'last_message' => $lastMessage ? [
                        'text' => $lastText,
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
     * Obtener conversación existente entre el usuario autenticado y otro usuario.
     * No crea conversación: solo devuelve la existente. La conversación se crea al enviar el primer mensaje.
     *
     * @param int    $otherUserId user_id del otro usuario
     * @param string $lang        Idioma para mensajes
     *
     * @return array{success: bool, conversation_id?: int|null, other_user?: array, error?: string}
     */
    public function ObtenerOcrearConversacion(int $otherUserId, string $lang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
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
            if ($conv) {
                $conv->removeHiddenForUser($userId);
                $this->em->persist($conv);
                $this->em->flush();

                return [
                    'success' => true,
                    'conversation_id' => $conv->getConversationId(),
                    'other_user' => $this->formatearUsuarioCorto($otherUser),
                ];
            }

            return [
                'success' => true,
                'conversation_id' => null,
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
     * @param string $lang Idioma del usuario (es|en) para devolver body en ese idioma
     *
     * @return array{success: bool, messages?: array, other_user?: array, error?: string}
     */
    public function ListarMensajes(int $conversationId, string $lang = 'es', ?int $limit = 100, int $offset = 0): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
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
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        try {
            // Al listar mensajes, marcar como leídos los que el usuario recibió (tiene sentido: ya los está viendo)
            $this->messageRepository->MarcarComoLeidos($conv, $userId);

            $messages = $this->messageRepository->ListarPorConversacion($conv, $limit, $offset);
            $list = [];
            foreach ($messages as $m) {
                if ($m->isDeletedForUser($userId)) {
                    continue;
                }
                $senderId = $m->getSender() ? $m->getSender()->getUsuarioId() : null;
                $isOwn = $senderId === $userId;
                $text = null !== $m->getDeletedForEveryoneAt()
                   ? 'message_deleted_placeholder'
                   : ($isOwn
                      ? ($m->getBodyOriginal() ?? $m->getBodyForLang($lang))
                      : ($m->getBodyForLang($lang) ?? $m->getBodyOriginal()));
                $list[] = [
                    'message_id' => $m->getMessageId(),
                    'sender_id' => $senderId,
                    'text' => $text,
                    'created_at' => $m->getCreatedAt() ? $m->getCreatedAt()->format('c') : null,
                    'read_at' => $m->getReadAt() ? $m->getReadAt()->format('c') : null,
                    'deleted_for_everyone' => null !== $m->getDeletedForEveryoneAt(),
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
     * @param string $body       Texto del mensaje
     * @param string $sourceLang es|en
     *
     * @return array{success: bool, message?: array, error?: string}
     */
    public function EnviarMensaje(int $conversationId, string $body, string $sourceLang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }

        $conv = $this->conversationRepository->find($conversationId);
        if (!$conv instanceof MessageConversation) {
            return ['success' => false, 'error' => 'Conversación no encontrada'];
        }
        if (!$conv->getOtherUser($usuario)) {
            return ['success' => false, 'error' => 'No pertenece a esta conversación'];
        }

        $body = trim($body);
        if ('' === $body) {
            return ['success' => false, 'error' => 'El mensaje no puede estar vacío'];
        }
        $sourceLang = 'en' === $sourceLang ? 'en' : 'es';

        try {
            $message = new Message();
            $message->setConversation($conv);
            $message->setSender($usuario);
            $message->setBodyOriginal($body);
            $message->setSourceLang($sourceLang);
            // Sin traducción automática: se guarda el mismo texto en ambos idiomas (traducción a petición del usuario).
            $message->setBodyEs($body);
            $message->setBodyEn($body);
            $now = new \DateTime();
            $message->setCreatedAt($now);
            $conv->setUpdatedAt($now);
            $this->em->persist($message);
            $this->em->flush();

            // Notificación push al destinatario (otro usuario de la conversación)
            $recipient = $conv->getOtherUser($usuario);
            if (null !== $recipient->getPushToken() && '' !== $recipient->getPushToken()) {
                $senderName = $usuario->getNombreCompleto();
                $preview = mb_strlen($body) > 80 ? mb_substr($body, 0, 77).'...' : $body;
                $this->pushNotificationService->sendToDevice(
                    $recipient->getPushToken(),
                    $senderName,
                    $preview,
                    [
                        'conversation_id' => (string) $conv->getConversationId(),
                        'message_id' => (string) $message->getMessageId(),
                    ]
                );
            }

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
     * Enviar el primer mensaje a un usuario: crea la conversación si no existe y envía el mensaje.
     * Es el único punto donde se crea una nueva fila en message_conversation.
     *
     * @param int    $otherUserId user_id del destinatario
     * @param string $body        Texto del mensaje
     * @param string $sourceLang  es|en
     *
     * @return array{success: bool, conversation_id?: int, message?: array, error?: string}
     */
    public function EnviarPrimerMensaje(int $otherUserId, string $body, string $sourceLang = 'es'): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }
        if ($otherUserId === $userId) {
            return ['success' => false, 'error' => 'No puede enviar mensaje a sí mismo'];
        }

        $otherUser = $this->usuarioRepository->find($otherUserId);
        if (!$otherUser instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario destinatario no encontrado'];
        }

        $body = trim($body);
        if ('' === $body) {
            return ['success' => false, 'error' => 'El mensaje no puede estar vacío'];
        }
        $sourceLang = 'en' === $sourceLang ? 'en' : 'es';

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

            $sendResult = $this->EnviarMensaje($conv->getConversationId(), $body, $sourceLang);
            if (!$sendResult['success']) {
                return $sendResult;
            }

            return [
                'success' => true,
                'conversation_id' => $conv->getConversationId(),
                'message' => $sendResult['message'],
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
     *
     * @return array{success: bool, users?: array, error?: string}
     */
    public function ListarUsuariosParaChat(string $search = ''): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }
        try {
            $usuarios = $this->usuarioRepository->ListarOrdenados($search, '', '');
            $list = [];
            foreach ($usuarios as $u) {
                if ($u->getUsuarioId() === $userId) {
                    continue;
                }
                // Solo usuarios con permiso de chat pueden aparecer en el selector
                if (!$u->getChat()) {
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
     * @return array{success: bool, error?: string}
     */
    public function MarcarComoLeidos(int $conversationId): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
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

    /**
     * Eliminar mensaje "para mí": solo deja de mostrarse al usuario que elimina.
     *
     * @return array{success: bool, error?: string}
     */
    public function EliminarMensajeParaMi(int $messageId, int $conversationId): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        $message = $this->messageRepository->find($messageId);
        if (!$message instanceof Message) {
            return ['success' => false, 'error' => 'Mensaje no encontrado'];
        }
        $conv = $message->getConversation();
        if (!$conv instanceof MessageConversation || $conv->getConversationId() !== $conversationId) {
            return ['success' => false, 'error' => 'Mensaje no pertenece a esta conversación'];
        }
        if (!$conv->getOtherUser($usuario)) {
            return ['success' => false, 'error' => 'No pertenece a esta conversación'];
        }

        try {
            $message->addDeletedForUser($userId);
            $this->em->persist($message);
            $this->em->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Eliminar mensaje "para todos": se muestra placeholder "Este mensaje fue eliminado".
     * Solo permitido si el usuario es el remitente y el mensaje fue enviado hace menos de 1 hora.
     *
     * @return array{success: bool, error?: string}
     */
    public function EliminarMensajeParaTodos(int $messageId, int $conversationId): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
            return ['success' => false, 'error' => 'Usuario no válido'];
        }

        $message = $this->messageRepository->find($messageId);
        if (!$message instanceof Message) {
            return ['success' => false, 'error' => 'Mensaje no encontrado'];
        }
        $conv = $message->getConversation();
        if (!$conv instanceof MessageConversation || $conv->getConversationId() !== $conversationId) {
            return ['success' => false, 'error' => 'Mensaje no pertenece a esta conversación'];
        }
        $sender = $message->getSender();
        if (!$sender || $sender->getUsuarioId() !== $userId) {
            return ['success' => false, 'error' => 'Solo el remitente puede eliminar el mensaje para todos'];
        }
        if (null !== $message->getDeletedForEveryoneAt()) {
            return ['success' => false, 'error' => 'El mensaje ya fue eliminado'];
        }

        $createdAt = $message->getCreatedAt();
        if (null === $createdAt) {
            return ['success' => false, 'error' => 'Fecha del mensaje no válida'];
        }
        $elapsed = (new \DateTime())->getTimestamp() - $createdAt->getTimestamp();
        if ($elapsed > self::DELETE_FOR_EVERYONE_WINDOW_SECONDS) {
            return ['success' => false, 'error' => 'delete_for_everyone_expired'];
        }

        try {
            $message->setDeletedForEveryoneAt(new \DateTime());
            $this->em->persist($message);
            $this->em->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Ocultar conversación y borrar historial para el usuario (eliminar chat, estilo WhatsApp).
     * La conversación se oculta de la lista y todos los mensajes se marcan como "eliminados para mí".
     *
     * @return array{success: bool, error?: string}
     */
    public function OcultarConversacion(int $conversationId): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $userId = $usuario->getUsuarioId();
        if (null === $userId) {
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
            $conv->addHiddenForUser($userId);
            $this->em->persist($conv);
            $this->em->flush();

            $this->messageRepository->MarcarTodosComoEliminadosParaUsuario($conv, $userId);

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
            'name' => trim(($u->getNombre() ?? '').' '.($u->getApellidos() ?? '')),
            'image' => $u->getImagen() ?? '',
        ];
    }

    /**
     * Traducción a petición del usuario. Comprueba permiso chat y límite mensual (500k) cuando se persiste en un mensaje.
     * Si se pasan message_id y conversation_id, guarda la traducción en body_es/body_en del mensaje y actualiza translated_at.
     *
     * @param int|null $messageId      Id del mensaje para persistir la traducción (opcional; ej. mensajes pendientes no tienen id)
     * @param int|null $conversationId Id de la conversación (requerido si se pasa message_id)
     *
     * @return array{success: bool, translated_text?: string, error?: string}
     */
    public function TraducirOnDemand(string $text, string $targetLang, ?int $messageId = null, ?int $conversationId = null): array
    {
        $usuario = $this->getUser();
        if (!$usuario instanceof Usuario) {
            return ['success' => false, 'error' => 'Usuario no autenticado'];
        }
        $forbidden = $this->checkPermisoChat($usuario);
        if (null !== $forbidden) {
            return $forbidden;
        }
        $text = trim($text);
        if ('' === $text) {
            return ['success' => false, 'error' => 'El texto está vacío'];
        }
        $targetLang = 'en' === $targetLang ? 'en' : 'es';

        $message = null;
        if (null !== $messageId && null !== $conversationId) {
            $conv = $this->conversationRepository->find($conversationId);
            if (!$conv instanceof MessageConversation || !$conv->getOtherUser($usuario)) {
                return ['success' => false, 'error' => 'Conversación no encontrada o no pertenece'];
            }
            $message = $this->messageRepository->find($messageId);
            if (!$message instanceof Message || $message->getConversation()?->getConversationId() !== $conversationId) {
                return ['success' => false, 'error' => 'Mensaje no encontrado'];
            }
            // Caché: solo usar body_es/body_en si el mensaje fue traducido antes (translated_at).
            // Al enviar se copia el mismo texto a body_es y body_en; eso NO es una traducción real.
            if (null !== $message->getTranslatedAt()) {
                $existing = $message->getBodyForLang($targetLang);
                if (null !== $existing && '' !== $existing) {
                    return ['success' => true, 'translated_text' => $existing];
                }
            }
        }

        // Glosario: términos técnicos inglés→español (evita traducciones incorrectas de Google)
        $glossaryResult = $this->translateWithGlossary($text, $targetLang);
        if (null !== $glossaryResult) {
            if (null !== $message) {
                $this->persistTranslatedBody($message, $targetLang, $glossaryResult);
            }

            return ['success' => true, 'translated_text' => $glossaryResult];
        }

        if ('' === $this->googleTranslateApiKey) {
            $translated = $text;
            if (null !== $message) {
                $this->persistTranslatedBody($message, $targetLang, $translated);
            }

            return ['success' => true, 'translated_text' => $translated];
        }

        $charCount = mb_strlen($text);
        if (null !== $message) {
            $now = new \DateTimeImmutable();
            $startOfMonth = $now->modify('first day of this month')->setTime(0, 0, 0);
            $usedThisMonth = $this->messageRepository->sumTranslatedCharactersForPeriod($startOfMonth, $now);
            if (($usedThisMonth + $charCount) > self::TRANSLATE_LIMIT_CHARS_PER_MONTH) {
                return ['success' => false, 'error' => 'Límite mensual de traducción alcanzado'];
            }
        }

        try {
            $textToTranslate = $text;
            $placeholders = [];
            $glossary = $this->getGlossaryEnEs();
            if ('es' === $targetLang && [] !== $glossary) {
                $textToTranslate = $this->replaceGlossaryTermsWithPlaceholders($text, $glossary, $placeholders);
            }

            $payload = [
                'q' => $textToTranslate,
                'target' => $targetLang,
                'format' => 'text',
            ];
            $response = $this->httpClient->request('POST', self::GOOGLE_TRANSLATE_API_URL, [
                'query' => ['key' => $this->googleTranslateApiKey],
                'json' => $payload,
            ]);
            $data = $response->toArray();
            $translated = $data['data']['translations'][0]['translatedText'] ?? null;
            if (null === $translated) {
                $translated = $text;
            }
            if ([] !== $placeholders) {
                $translated = $this->replacePlaceholdersWithGlossary($translated, $placeholders);
            }
            if (null !== $message) {
                $this->persistTranslatedBody($message, $targetLang, $translated);
            }

            return ['success' => true, 'translated_text' => $translated];
        } catch (\Throwable $e) {
            $this->logger->warning('Translation on-demand failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function persistTranslatedBody(Message $message, string $targetLang, string $translated): void
    {
        if ('en' === $targetLang) {
            $message->setBodyEn($translated);
        } else {
            $message->setBodyEs($translated);
        }
        $message->setTranslatedAt(new \DateTimeImmutable());
        $this->em->persist($message);
        $this->em->flush();
    }

    /**
     * Traduce usando el glosario de términos técnicos. Si el texto coincide exactamente con una clave,
     * devuelve la traducción. Si contiene términos del glosario, los reemplaza. Si no hay coincidencias,
     * devuelve null para que se use Google.
     *
     * @return string|null La traducción o null si debe usarse Google
     */
    private function translateWithGlossary(string $text, string $targetLang): ?string
    {
        if ('es' !== $targetLang) {
            return null;
        }
        $glossary = $this->getGlossaryEnEs();
        if ([] === $glossary) {
            return null;
        }
        $lower = mb_strtolower(trim($text));
        foreach ($glossary as $en => $es) {
            if (mb_strtolower(trim($en)) === $lower) {
                return $es;
            }
        }

        return null;
    }

    /** @return array<string, string> Términos inglés => español (desde translations/messages+intl-icu.*.yaml) */
    private function getGlossaryEnEs(): array
    {
        $pathEn = $this->projectDir.'/'.self::TRANSLATIONS_EN;
        $pathEs = $this->projectDir.'/'.self::TRANSLATIONS_ES;
        if ('' === $this->projectDir || !is_file($pathEn) || !is_file($pathEs)) {
            return [];
        }
        try {
            $dataEn = Yaml::parseFile($pathEn);
            $dataEs = Yaml::parseFile($pathEs);
        } catch (\Throwable) {
            return [];
        }
        $glossaryEn = $dataEn['glossary'] ?? [];
        $glossaryEs = $dataEs['glossary'] ?? [];
        if (!is_array($glossaryEn) || !is_array($glossaryEs)) {
            return [];
        }
        $result = [];
        foreach ($glossaryEn as $key => $enTerm) {
            if (is_string($enTerm) && isset($glossaryEs[$key]) && is_string($glossaryEs[$key])) {
                $result[$enTerm] = $glossaryEs[$key];
            }
        }

        return $result;
    }

    /**
     * Reemplaza términos del glosario con placeholders para que Google no los traduzca.
     * Orden: claves más largas primero para evitar "catch" antes de "catch basin".
     *
     * @param array<string, string> $glossary
     * @param array<string, string> $placeholders Se rellena con placeholder => traducción
     */
    private function replaceGlossaryTermsWithPlaceholders(string $text, array $glossary, array &$placeholders): string
    {
        $placeholders = [];
        $keys = array_keys($glossary);
        usort($keys, static fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $result = $text;
        $i = 0;
        foreach ($keys as $en) {
            $placeholder = '§G'.$i.'§';
            $placeholders[$placeholder] = $glossary[$en];
            $result = preg_replace('/\b'.preg_quote($en, '/').'\b/iu', $placeholder, $result);
            ++$i;
        }

        return $result;
    }

    /** @param array<string, string> $placeholders placeholder => traducción */
    private function replacePlaceholdersWithGlossary(string $text, array $placeholders): string
    {
        foreach ($placeholders as $ph => $translation) {
            $text = str_replace($ph, $translation, $text);
        }

        return $text;
    }
}
