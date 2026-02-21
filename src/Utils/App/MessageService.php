<?php

namespace App\Utils\App;

use App\Entity\Message;
use App\Entity\MessageConversation;
use App\Entity\Usuario;
use App\Repository\MessageConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UsuarioRepository;
use App\Service\PushNotificationService;
use App\Utils\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

   private const TRANSLATE_LIMIT_CHARS_PER_MONTH = 500_000;
   private const GOOGLE_TRANSLATE_API_URL = 'https://translation.googleapis.com/language/translate/v2';

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
      PushNotificationService $pushNotificationService,
      HttpClientInterface $httpClient,
      string $googleTranslateApiKey = ''
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->conversationRepository = $conversationRepository;
      $this->messageRepository = $messageRepository;
      $this->usuarioRepository = $usuarioRepository;
      $this->em = $em;
      $this->pushNotificationService = $pushNotificationService;
      $this->httpClient = $httpClient;
      $this->googleTranslateApiKey = $googleTranslateApiKey;
   }

   /**
    * Listar conversaciones del usuario autenticado (para lista de chats).
    * Estructura compatible con frontend: other_user, last_message, unread_count.
    *
    * @param string $lang Idioma del usuario (es|en) para devolver el texto del último mensaje en ese idioma
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
      if ($forbidden !== null) {
         return $forbidden;
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
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
         return $forbidden;
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
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
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
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
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
         if ($recipient instanceof Usuario && $recipient->getPushToken() !== null && $recipient->getPushToken() !== '') {
            $senderName = $usuario->getNombreCompleto();
            $preview = mb_strlen($body) > 80 ? mb_substr($body, 0, 77) . '...' : $body;
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
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
         return $forbidden;
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
    * @param int $conversationId
    * @return array{success: bool, error?: string}
    */
   public function MarcarComoLeidos(int $conversationId): array
   {
      $usuario = $this->getUser();
      if (!$usuario instanceof Usuario) {
         return ['success' => false, 'error' => 'Usuario no autenticado'];
      }
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
         return $forbidden;
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
    * Traducción a petición del usuario. Comprueba permiso chat y límite mensual (500k) cuando se persiste en un mensaje.
    * Si se pasan message_id y conversation_id, guarda la traducción en body_es/body_en del mensaje y actualiza translated_at.
    *
    * @param int|null $messageId      Id del mensaje para persistir la traducción (opcional; ej. mensajes pendientes no tienen id)
    * @param int|null $conversationId Id de la conversación (requerido si se pasa message_id)
    * @return array{success: bool, translated_text?: string, error?: string}
    */
   public function TraducirOnDemand(string $text, string $targetLang, ?int $messageId = null, ?int $conversationId = null): array
   {
      $usuario = $this->getUser();
      if (!$usuario instanceof Usuario) {
         return ['success' => false, 'error' => 'Usuario no autenticado'];
      }
      $forbidden = $this->checkPermisoChat($usuario);
      if ($forbidden !== null) {
         return $forbidden;
      }
      $text = trim($text);
      if ($text === '') {
         return ['success' => false, 'error' => 'El texto está vacío'];
      }
      $targetLang = $targetLang === 'en' ? 'en' : 'es';

      $message = null;
      if ($messageId !== null && $conversationId !== null) {
         $conv = $this->conversationRepository->find($conversationId);
         if (!$conv instanceof MessageConversation || !$conv->getOtherUser($usuario)) {
            return ['success' => false, 'error' => 'Conversación no encontrada o no pertenece'];
         }
         $message = $this->messageRepository->find($messageId);
         if (!$message instanceof Message || $message->getConversation()?->getConversationId() !== $conversationId) {
            return ['success' => false, 'error' => 'Mensaje no encontrado'];
         }
      }

      if ($this->googleTranslateApiKey === '') {
         $translated = $text;
         if ($message !== null) {
            $this->persistTranslatedBody($message, $targetLang, $translated);
         }
         return ['success' => true, 'translated_text' => $translated];
      }

      $charCount = mb_strlen($text);
      if ($message !== null) {
         $now = new \DateTimeImmutable();
         $startOfMonth = $now->modify('first day of this month')->setTime(0, 0, 0);
         $usedThisMonth = $this->messageRepository->sumTranslatedCharactersForPeriod($startOfMonth, $now);
         if (($usedThisMonth + $charCount) > self::TRANSLATE_LIMIT_CHARS_PER_MONTH) {
            return ['success' => false, 'error' => 'Límite mensual de traducción alcanzado'];
         }
      }

      try {
         $payload = [
            'q'      => $text,
            'target' => $targetLang,
            'format' => 'text',
         ];
         $response = $this->httpClient->request('POST', self::GOOGLE_TRANSLATE_API_URL, [
            'query' => ['key' => $this->googleTranslateApiKey],
            'json'  => $payload,
         ]);
         $data = $response->toArray();
         $translated = $data['data']['translations'][0]['translatedText'] ?? null;
         if ($translated === null) {
            $translated = $text;
         }
         if ($message !== null) {
            $this->persistTranslatedBody($message, $targetLang, $translated);
         }
         return ['success' => true, 'translated_text' => $translated];
      } catch (\Throwable $e) {
         $this->logger->warning('Translation on-demand failed: ' . $e->getMessage());
         return ['success' => false, 'error' => $e->getMessage()];
      }
   }

   private function persistTranslatedBody(Message $message, string $targetLang, string $translated): void
   {
      if ($targetLang === 'en') {
         $message->setBodyEn($translated);
      } else {
         $message->setBodyEs($translated);
      }
      $message->setTranslatedAt(new \DateTimeImmutable());
      $this->em->persist($message);
      $this->em->flush();
   }
}
