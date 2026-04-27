<?php

namespace App\Controller\App;

use App\Controller\App\Traits\ApiValidationResponseTrait;
use App\Controller\App\Traits\SetsTranslatorLocaleTrait;
use App\Dto\Api\Messaging\EliminarMensajeRequest;
use App\Dto\Api\Messaging\EnviarMensajeRequest;
use App\Dto\Api\Messaging\EnviarPrimerMensajeRequest;
use App\Dto\Api\Messaging\MarcarLeidosRequest;
use App\Dto\Api\Messaging\OcultarConversacionRequest;
use App\Dto\Api\Messaging\TraducirMensajeRequest;
use App\Service\App\MessageService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Mensajería interna para la app.
 *
 * Idioma y traducción:
 * - El idioma del usuario que VE los mensajes viene en la URL: /api/{lang}/message/...
 *   La app envía en cada petición su idioma actual (TranslateService.currentLang).
 * - Al ENVIAR: el cliente manda body + source_lang (idioma del remitente). El backend
 *   guarda body_original, body_es y body_en (traduce al otro idioma si hay API key).
 * - Al LISTAR conversaciones/mensajes: se devuelve el texto (body_es o body_en) según
 *   el {lang} de la petición. No se consulta el idioma del destinatario en BD.
 */
#[OA\Tag(name: 'Message', description: 'Internal messaging endpoints for mobile app')]
class MessageController extends AbstractController
{
    use ApiValidationResponseTrait;
    use SetsTranslatorLocaleTrait;
    private MessageService $messageService;
    private TranslatorInterface $translator;

    public function __construct(
        MessageService $messageService,
        TranslatorInterface $translator,
        private ValidatorInterface $validator,
    ) {
        $this->messageService = $messageService;
        $this->translator = $translator;
    }

    /**
     * Listar usuarios para iniciar chat (excluye al actual). Opcional: search.
     */
    #[OA\Get(
        path: '/api/{lang}/message/usuarios',
        summary: 'List users for chat',
        description: 'Returns users that can be messaged (for "new chat" screen). Optional search query. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'users', type: 'array', items: new OA\Items(type: 'object')),
            ])),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function listarUsuarios(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        $search = (string) $request->query->get('search', '');
        $result = $this->messageService->ListarUsuariosParaChat($search);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Listar conversaciones del usuario (lista de chats).
     */
    #[OA\Get(
        path: '/api/{lang}/message/conversaciones',
        summary: 'List conversations',
        description: 'Returns the list of conversations for the authenticated user. Each item includes other_user, last_message and unread_count. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'conversations', type: 'array', items: new OA\Items(type: 'object')),
            ])),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function listarConversaciones(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        $result = $this->messageService->ListarConversaciones($lang);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Obtener o crear conversación con otro usuario.
     */
    #[OA\Get(
        path: '/api/{lang}/message/conversacion',
        summary: 'Get or create conversation',
        description: 'Returns existing conversation with another user or creates it. Use other_user_id query param. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en'])),
            new OA\Parameter(name: 'other_user_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'conversation_id', type: 'integer'),
                new OA\Property(property: 'other_user', type: 'object'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function obtenerOcrearConversacion(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        $otherUserId = (int) $request->query->get('other_user_id', 0);
        if ($otherUserId <= 0) {
            return $this->json(['success' => false, 'error' => 'other_user_id is required'], 400);
        }
        $result = $this->messageService->ObtenerOcrearConversacion($otherUserId, $lang);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Listar mensajes de una conversación (inbox/chat).
     */
    #[OA\Get(
        path: '/api/{lang}/message/mensajes',
        summary: 'List messages',
        description: 'Returns messages of a conversation. Use conversation_id, optional limit and offset. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en'])),
            new OA\Parameter(name: 'conversation_id', in: 'query', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 100)),
            new OA\Parameter(name: 'offset', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'messages', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(property: 'other_user', type: 'object'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function listarMensajes(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        $conversationId = (int) $request->query->get('conversation_id', 0);
        if ($conversationId <= 0) {
            return $this->json(['success' => false, 'error' => 'conversation_id is required'], 400);
        }
        $limit = (int) $request->query->get('limit', 100);
        $offset = (int) $request->query->get('offset', 0);
        $result = $this->messageService->ListarMensajes($conversationId, $lang, $limit > 0 ? $limit : null, $offset >= 0 ? $offset : 0);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Enviar mensaje. Body JSON: conversation_id, body, source_lang (es|en).
     */
    #[OA\Post(
        path: '/api/{lang}/message/enviar',
        summary: 'Send message',
        description: 'Sends a message in a conversation. JSON body: conversation_id, body, source_lang (es|en). Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en']))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['conversation_id', 'body'], properties: [
                new OA\Property(property: 'conversation_id', type: 'integer'),
                new OA\Property(property: 'body', type: 'string'),
                new OA\Property(property: 'source_lang', type: 'string', enum: ['es', 'en']),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'message', type: 'object'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function enviarMensaje(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = EnviarMensajeRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $sourceLang = $payload->source_lang ?? 'es';
        $result = $this->messageService->EnviarMensaje($payload->conversation_id, (string) $payload->body, $sourceLang);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Enviar primer mensaje a un usuario: crea la conversación si no existe y envía el mensaje.
     * Body JSON: other_user_id, body, source_lang (es|en).
     */
    #[OA\Post(
        path: '/api/{lang}/message/enviar-primer-mensaje',
        summary: 'Send first message',
        description: 'Creates the conversation if it does not exist and sends the first message. JSON body: other_user_id, body, source_lang. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en']))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['other_user_id', 'body'], properties: [
                new OA\Property(property: 'other_user_id', type: 'integer'),
                new OA\Property(property: 'body', type: 'string'),
                new OA\Property(property: 'source_lang', type: 'string', enum: ['es', 'en']),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'conversation_id', type: 'integer'),
                new OA\Property(property: 'message', type: 'object'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function enviarPrimerMensaje(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = EnviarPrimerMensajeRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $sourceLang = $payload->source_lang ?? 'es';
        $result = $this->messageService->EnviarPrimerMensaje($payload->other_user_id, (string) $payload->body, $sourceLang);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Marcar como leídos los mensajes de una conversación.
     */
    #[OA\Post(
        path: '/api/{lang}/message/marcar-leidos',
        summary: 'Mark as read',
        description: 'Marks all messages in a conversation as read for the current user. JSON body: conversation_id. Requires Bearer token.',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en']))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['conversation_id'], properties: [
                new OA\Property(property: 'conversation_id', type: 'integer'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function marcarComoLeidos(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = MarcarLeidosRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $result = $this->messageService->MarcarComoLeidos($payload->conversation_id);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Traducir texto a petición del usuario (no se traduce al enviar el mensaje).
     */
    #[OA\Post(
        path: '/api/{lang}/message/traducir',
        summary: 'Translate text on demand',
        description: 'Translates the given text to the target language. Used when the user taps "Translate" on a message. Monthly character limit applies. Requires Bearer token and chat permission.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'lang', in: 'path', required: true, description: 'Language code (es or en)', schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['text'], properties: [
                new OA\Property(property: 'text', type: 'string', description: 'Text to translate'),
                new OA\Property(property: 'target_lang', type: 'string', description: 'Target language: es or en (default es)'),
                new OA\Property(property: 'message_id', type: 'integer', description: 'Optional. Message ID to store translation in body_es/body_en'),
                new OA\Property(property: 'conversation_id', type: 'integer', description: 'Required if message_id is sent'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'translated_text', type: 'string'),
            ])),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Chat not allowed'),
        ]
    )]
    public function traducir(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = TraducirMensajeRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $targetLang = $payload->target_lang ?? 'es';
        $messageId = $payload->message_id;
        $conversationId = $payload->conversation_id;
        $result = $this->messageService->TraducirOnDemand((string) $payload->text, $targetLang, $messageId, $conversationId);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Eliminar mensaje "para mí" o "para todos". Body JSON: message_id, conversation_id, scope ('for_me'|'for_everyone').
     */
    #[OA\Post(
        path: '/api/{lang}/message/eliminar-mensaje',
        summary: 'Delete message',
        description: 'Delete message for me (scope: for_me) or for everyone (scope: for_everyone). For everyone: only sender, within 1 hour. JSON body: message_id, conversation_id, scope.',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en']))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['message_id', 'conversation_id', 'scope'], properties: [
                new OA\Property(property: 'message_id', type: 'integer'),
                new OA\Property(property: 'conversation_id', type: 'integer'),
                new OA\Property(property: 'scope', type: 'string', enum: ['for_me', 'for_everyone']),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function eliminarMensaje(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = EliminarMensajeRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $result = 'for_me' === $payload->scope
           ? $this->messageService->EliminarMensajeParaMi($payload->message_id, $payload->conversation_id)
           : $this->messageService->EliminarMensajeParaTodos($payload->message_id, $payload->conversation_id);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    /**
     * Ocultar conversación (eliminar chat de la lista).
     */
    #[OA\Post(
        path: '/api/{lang}/message/ocultar-conversacion',
        summary: 'Hide conversation',
        description: 'Hides the conversation from the user chat list. JSON body: conversation_id.',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'lang', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['es', 'en']))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['conversation_id'], properties: [
                new OA\Property(property: 'conversation_id', type: 'integer'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function ocultarConversacion(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);
        try {
            $payload = OcultarConversacionRequest::fromHttpRequest($request);
        } catch (\Exception $e) {
            return $this->jsonJsonInputError($e);
        }
        $violations = $this->validator->validate($payload);
        if (\count($violations) > 0) {
            return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
        }
        $result = $this->messageService->OcultarConversacion($payload->conversation_id);
        if (!$result['success']) {
            $status = ($result['error'] ?? '') === 'chat_forbidden' ? 403 : 400;

            return $this->json($result, $status);
        }

        return $this->json($result);
    }

    private function jsonJsonInputError(\Exception $e): JsonResponse
    {
        if (str_contains($e->getMessage(), 'Content-Type') || str_contains($e->getMessage(), 'Invalid JSON')) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        throw $e;
    }
}
