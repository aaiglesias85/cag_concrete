<?php

namespace App\Controller\App;

use App\Utils\App\LoginService;
use App\Utils\App\MessageService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    private LoginService $loginService;
    private MessageService $messageService;
    private TranslatorInterface $translator;

    public function __construct(LoginService $loginService, MessageService $messageService, TranslatorInterface $translator)
    {
        $this->loginService = $loginService;
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
        $this->translator->setLocale($lang);
        $search = (string) $request->query->get('search', '');
        $result = $this->messageService->ListarUsuariosParaChat($search);
        if (!$result['success']) {
            return $this->json($result, 400);
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
        $this->translator->setLocale($lang);
        $result = $this->messageService->ListarConversaciones($lang);
        if (!$result['success']) {
            return $this->json($result, 400);
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
        $this->translator->setLocale($lang);
        $otherUserId = (int) $request->query->get('other_user_id', 0);
        if ($otherUserId <= 0) {
            return $this->json(['success' => false, 'error' => 'other_user_id is required'], 400);
        }
        $result = $this->messageService->ObtenerOcrearConversacion($otherUserId, $lang);
        if (!$result['success']) {
            return $this->json($result, 400);
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
        $this->translator->setLocale($lang);
        $conversationId = (int) $request->query->get('conversation_id', 0);
        if ($conversationId <= 0) {
            return $this->json(['success' => false, 'error' => 'conversation_id is required'], 400);
        }
        $limit = (int) $request->query->get('limit', 100);
        $offset = (int) $request->query->get('offset', 0);
        $result = $this->messageService->ListarMensajes($conversationId, $lang, $limit > 0 ? $limit : null, $offset >= 0 ? $offset : 0);
        if (!$result['success']) {
            return $this->json($result, 400);
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
        $this->translator->setLocale($lang);
        $data = json_decode($request->getContent(), true) ?? [];
        $conversationId = (int) ($data['conversation_id'] ?? 0);
        $body = trim((string) ($data['body'] ?? ''));
        $sourceLang = isset($data['source_lang']) && $data['source_lang'] === 'en' ? 'en' : 'es';
        if ($conversationId <= 0) {
            return $this->json(['success' => false, 'error' => 'conversation_id is required'], 400);
        }
        if ($body === '') {
            return $this->json(['success' => false, 'error' => 'body is required'], 400);
        }
        $result = $this->messageService->EnviarMensaje($conversationId, $body, $sourceLang);
        if (!$result['success']) {
            return $this->json($result, 400);
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
        $this->translator->setLocale($lang);
        $data = json_decode($request->getContent(), true) ?? [];
        $conversationId = (int) ($data['conversation_id'] ?? 0);
        if ($conversationId <= 0) {
            return $this->json(['success' => false, 'error' => 'conversation_id is required'], 400);
        }
        $result = $this->messageService->MarcarComoLeidos($conversationId);
        if (!$result['success']) {
            return $this->json($result, 400);
        }
        return $this->json($result);
    }
}
