<?php

namespace App\Controller\App;

use App\Controller\App\Traits\ApiValidationResponseTrait;
use App\Controller\App\Traits\SetsTranslatorLocaleTrait;
use App\Dto\Api\Request\Offline\OfflineSincronizarRequest;
use App\Dto\Api\Response\Offline\OfflineListarInformacionResponse;
use App\Dto\Api\Response\Offline\OfflineSincronizarResponse;
use App\Dto\Api\Response\Common\ApiSimpleFailureResponse;
use App\Service\App\LoginService;
use App\Service\App\OfflineService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Tag(name: 'Offline', description: 'Offline data synchronization endpoints')]
class OfflineController extends AbstractController
{
    use ApiValidationResponseTrait;
    use SetsTranslatorLocaleTrait;
    private LoginService $loginService;
    private OfflineService $offlineService;
    private TranslatorInterface $translator;

    public function __construct(
        LoginService $loginService,
        OfflineService $offlineService,
        TranslatorInterface $translator,
        private ValidatorInterface $validator,
    ) {
        $this->loginService = $loginService;
        $this->offlineService = $offlineService;
        $this->translator = $translator;
    }

    /**
     * listarInformacionRequerida Lista la información requerida para trabajo offline de la app.
     * Los datos se cargan una vez cuando hay conexión y la app trabaja con ellos en memoria/storage.
     * Requiere autenticación via Bearer token.
     */
    #[OA\Get(
        path: '/api/{lang}/offline/listarInformacionRequerida',
        summary: 'List offline required data',
        description: 'Returns all data required for offline mode. The app loads this once when online and then works with the data in memory/storage. Currently returns companies. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lang',
                in: 'path',
                required: true,
                description: 'Language code (es or en)',
                schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data loaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'companies',
                            type: 'array',
                            description: 'Lista de companies (company_id, name, phone, address, contact_name, contact_email, email, website, created_at, updated_at)',
                            items: new OA\Items(type: 'object')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error loading data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function listarInformacionRequerida(string $lang = 'es'): JsonResponse
    {
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $resultado = $this->offlineService->ListarInformacionRequerida();

            if ($resultado['success']) {
                return $this->json(OfflineListarInformacionResponse::fromServiceResult($resultado));
            }

            return $this->json(OfflineListarInformacionResponse::fromServiceResult($resultado), 400);
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }

    /**
     * sincronizar Sincroniza los datos offline guardados localmente.
     */
    #[OA\Post(
        path: '/api/{lang}/offline/sincronizar',
        summary: 'Synchronize offline data',
        description: 'Synchronizes offline data that was saved locally when there was no connection. Currently supports user profile data, but can be extended for other data types. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'lang',
                in: 'path',
                required: true,
                description: 'Language code (es or en)',
                schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'JSON with offline profile data',
            content: new OA\JsonContent(
                required: ['profile_offline'],
                properties: [
                    new OA\Property(
                        property: 'profile_offline',
                        type: 'object',
                        description: 'Offline profile data containing: nombre, apellidos, email, telefono, passwordactual, password (optional), imagen (optional base64)',
                        properties: [
                            new OA\Property(property: 'nombre', type: 'string', example: 'John', description: 'User first name'),
                            new OA\Property(property: 'apellidos', type: 'string', example: 'Doe', description: 'User last name'),
                            new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com', description: 'User email address'),
                            new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+1234567890', description: 'User phone number'),
                            new OA\Property(property: 'passwordactual', type: 'string', nullable: true, example: 'oldpassword123', description: 'Current password (required only if changing password)'),
                            new OA\Property(property: 'password', type: 'string', nullable: true, example: 'newpassword123', description: 'New password (optional)'),
                            new OA\Property(property: 'imagen', type: 'string', nullable: true, format: 'base64', example: 'data:image/jpeg;base64,/9j/4AAQSkZJRg...', description: 'Profile image in base64 format (optional)'),
                        ]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data synchronized successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Los datos se sincronizaron correctamente'),
                        new OA\Property(
                            property: 'usuario',
                            type: 'object',
                            description: 'Updated user data',
                            properties: [
                                new OA\Property(property: 'usuario_id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'nombre', type: 'string', example: 'John'),
                                new OA\Property(property: 'apellidos', type: 'string', example: 'Doe'),
                                new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+1234567890'),
                                new OA\Property(property: 'imagen', type: 'string', nullable: true, example: 'photo123.jpeg'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error synchronizing data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'No hay datos para sincronizar'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function sincronizar(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $payload = OfflineSincronizarRequest::fromHttpRequest($request);
            $violations = $this->validator->validate($payload);
            if (\count($violations) > 0) {
                return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
            }

            $profile = $payload->profile_offline;
            $profile_offline = [
                'nombre' => $profile->nombre,
                'apellidos' => $profile->apellidos,
                'email' => $profile->email,
                'telefono' => $profile->telefono,
                'passwordactual' => $profile->passwordactual ?? '',
                'password' => $profile->password ?? '',
                'imagen' => $profile->imagen,
            ];

            // Sincronizar perfil del usuario (por ahora solo se soporta perfil)
            $resultado = $this->offlineService->SincronizarPerfilUsuario($profile_offline);

            if ($resultado['success']) {
                $resultado['message'] = $this->translator->trans('offline.message.sincronizado', [], 'messages', $lang);

                return $this->json(OfflineSincronizarResponse::fromPayload($resultado));
            }
            $resultado['error'] = $resultado['error'] ?? $this->translator->trans('offline.error.sincronizar_perfil', [], 'messages', $lang);

            return $this->json(OfflineSincronizarResponse::fromPayload($resultado), 400);
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }
}
