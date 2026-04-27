<?php

namespace App\Controller\App;

use App\Controller\App\Traits\ApiValidationResponseTrait;
use App\Controller\App\Traits\SetsTranslatorLocaleTrait;
use App\Dto\Api\Request\Usuario\ActualizarUsuarioDatosRequest;
use App\Dto\Api\Request\Usuario\SalvarImagenUsuarioRequest;
use App\Dto\Api\Response\Common\ApiSimpleFailureResponse;
use App\Dto\Api\Response\Common\ApiSimpleSuccessMessageResponse;
use App\Dto\Api\Response\Usuario\UsuarioActualizarDatosResponse;
use App\Dto\Api\Response\Usuario\UsuarioCargarDatosFailureResponse;
use App\Dto\Api\Response\Usuario\UsuarioCargarDatosSuccessResponse;
use App\Dto\Api\Response\Usuario\UsuarioSalvarImagenFailureResponse;
use App\Dto\Api\Response\Usuario\UsuarioSalvarImagenSuccessResponse;
use App\Entity\Usuario;
use App\Service\App\LoginService;
use App\Service\App\UsuarioService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Tag(name: 'User', description: 'User profile management endpoints')]
class UsuarioController extends AbstractController
{
    use ApiValidationResponseTrait;
    use SetsTranslatorLocaleTrait;
    private LoginService $loginService;
    private UsuarioService $usuarioService;
    private TranslatorInterface $translator;

    public function __construct(
        LoginService $loginService,
        UsuarioService $usuarioService,
        TranslatorInterface $translator,
        private ValidatorInterface $validator,
    ) {
        $this->loginService = $loginService;
        $this->usuarioService = $usuarioService;
        $this->translator = $translator;
    }

    /**
     * cargarDatos Acción que carga los datos del usuario en la BD.
     */
    #[OA\Get(
        path: '/api/usuario/cargarDatos',
        summary: 'Get authenticated user data',
        description: 'Returns the profile data of the currently authenticated user. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User data retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'usuario',
                            type: 'object',
                            description: 'Complete user data',
                            properties: [
                                new OA\Property(property: 'usuario_id', type: 'integer', example: 1),
                                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                                new OA\Property(property: 'nombre', type: 'string', example: 'John'),
                                new OA\Property(property: 'apellidos', type: 'string', example: 'Doe'),
                                new OA\Property(property: 'nombre_completo', type: 'string', example: 'John Doe'),
                                new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+1234567890'),
                                new OA\Property(property: 'imagen', type: 'string', nullable: true, example: 'photo123.jpeg', description: 'Profile image filename'),
                                new OA\Property(property: 'rol_id', type: 'integer', nullable: true, example: 2),
                                new OA\Property(property: 'rol', type: 'string', nullable: true, example: 'User'),
                                new OA\Property(property: 'chat', type: 'boolean', nullable: true, example: true, description: 'Whether the user can use the chat feature'),
                            ]
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
                        new OA\Property(property: 'error', type: 'string', example: 'Could not load user data'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function cargarDatos(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $resultado = $this->usuarioService->CargarDatosUsuario();

            if ($resultado['success']) {
                return $this->json(new UsuarioCargarDatosSuccessResponse($resultado['usuario']));
            }

            return $this->json(new UsuarioCargarDatosFailureResponse(
                $resultado['error'] ?? $this->translator->trans('usuario.error.cargar_datos', [], 'messages', $lang)
            ));
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }

    /**
     * actualizarDatos Acción que actualiza los datos del usuario en la BD
     * Permite actualizar datos generales y opcionalmente cambiar la contraseña.
     */
    #[OA\Post(
        path: '/api/usuario/actualizarDatos',
        summary: 'Update user profile data',
        description: 'Updates the profile data of the authenticated user. Allows updating name, last name, email, phone and optionally changing the password. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', example: 'John', description: 'User first name'),
                    new OA\Property(property: 'apellidos', type: 'string', example: 'Doe', description: 'User last name'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com', description: 'User email address'),
                    new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+1234567890', description: 'User phone number'),
                    new OA\Property(property: 'password_actual', type: 'string', format: 'password', nullable: true, example: 'oldpassword123', description: 'Current password (required only if changing password)'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', nullable: true, example: 'newpassword123', description: 'New password (required only if changing password)'),
                    new OA\Property(property: 'preferred_lang', type: 'string', enum: ['es', 'en'], nullable: true, description: 'Preferred app language'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'The operation was successful'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error updating data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Current password is incorrect'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function actualizarDatos(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $payload = ActualizarUsuarioDatosRequest::fromHttpRequest($request);
            $violations = $this->validator->validate($payload);
            if (\count($violations) > 0) {
                return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
            }

            $user = $this->getUser();
            if (!$user instanceof Usuario) {
                return $this->json(new ApiSimpleFailureResponse('Not authenticated'), 401);
            }
            // Si solo se envía preferred_lang, mantener el resto de datos del usuario
            $nombre = null !== $payload->nombre ? $payload->nombre : $user->getNombre();
            $apellidos = null !== $payload->apellidos ? $payload->apellidos : $user->getApellidos();
            $email = null !== $payload->email ? $payload->email : $user->getEmail();
            $telefono = null !== $payload->telefono ? $payload->telefono : $user->getTelefono();

            // Contraseñas opcionales (solo si se quiere cambiar)
            $password_actual = $payload->password_actual ?? '';
            $password = $payload->password ?? '';
            $preferred_lang = $payload->preferred_lang;

            // Actualizar datos (con o sin cambiar contraseña o idioma)
            $resultado = $this->usuarioService->ActualizarMisDatos(
                $nombre,
                $apellidos,
                $email,
                $telefono,
                $password_actual,
                $password,
                $preferred_lang
            );

            return $this->json(new UsuarioActualizarDatosResponse(
                (bool) $resultado['success'],
                $resultado['success'] ? $this->translator->trans('usuario.message.actualizado', [], 'messages', $lang) : null,
                $resultado['success'] ? null : (string) ($resultado['error'] ?? ''),
            ));
        } catch (\Exception $e) {
            // Si es error de formato JSON, retornar 400
            if (str_contains($e->getMessage(), 'Content-Type') || str_contains($e->getMessage(), 'Invalid JSON')) {
                return $this->json(new ApiSimpleFailureResponse($e->getMessage()), 400);
            }

            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }

    /**
     * salvarImagen Subir una imagen al servidor (base64).
     */
    #[OA\Post(
        path: '/api/usuario/salvarImagen',
        summary: 'Upload profile image',
        description: 'Uploads a user profile image in base64 format. The image is saved on the server and associated with the authenticated user. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['imagen'],
                properties: [
                    new OA\Property(
                        property: 'imagen',
                        type: 'string',
                        format: 'base64',
                        example: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...',
                        description: 'Image in base64 format. Must include the prefix data:image/[type];base64,'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image uploaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'imagen', type: 'string', example: 'photo123.jpeg', description: 'Saved image filename'),
                        new OA\Property(property: 'message', type: 'string', example: 'Operation completed successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error uploading image',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'No image provided'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function salvarImagen(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $usuario = $this->getUser();
            if (null == $usuario) {
                return $this->json(new UsuarioSalvarImagenFailureResponse(
                    $this->translator->trans('usuario.error.usuario_no_existe', [], 'messages', $lang)
                ));
            }

            $payload = SalvarImagenUsuarioRequest::fromHttpRequest($request);
            $violations = $this->validator->validate($payload);
            if (\count($violations) > 0) {
                return $this->json($this->formatValidationFailure($violations), Response::HTTP_BAD_REQUEST);
            }

            $imagen = $payload->imagen;

            $binary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen), true);
            if (false === $binary || '' === $binary) {
                return $this->json(new UsuarioSalvarImagenFailureResponse(
                    $this->translator->trans('usuario.error.decodificar_imagen', [], 'messages', $lang)
                ));
            }

            // Crear directorio si no existe
            $dir = 'uploads/usuario/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Generar nombre único para la imagen
            $foto = $this->usuarioService->generarCadenaAleatoria().'.jpeg';
            $ruta_completa = $dir.$foto;

            file_put_contents($ruta_completa, $binary);

            // Actualizar imagen del usuario en BD (a través del servicio)
            $resultado = $this->usuarioService->ActualizarImagenPerfil($foto);

            if ($resultado['success']) {
                return $this->json(new UsuarioSalvarImagenSuccessResponse(
                    $foto,
                    $this->translator->trans('usuario.message.imagen_guardada', [], 'messages', $lang)
                ));
            }

            return $this->json(new UsuarioSalvarImagenFailureResponse(
                $resultado['error'] ?? $this->translator->trans('usuario.error.actualizar_imagen', [], 'messages', $lang)
            ));
        } catch (\Exception $e) {
            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }

    /**
     * eliminarImagen Acción que elimina una imagen en la BD.
     */
    #[OA\Post(
        path: '/api/usuario/eliminarImagen',
        summary: 'Delete profile image',
        description: 'Deletes the profile image of the authenticated user. Requires authentication via Bearer token.',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Operation completed successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error deleting image',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Error deleting image'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
            new OA\Response(response: 500, description: 'Internal server error'),
        ]
    )]
    public function eliminarImagen(Request $request, string $lang = 'es'): JsonResponse
    {
        $request->setLocale($lang);
        $this->setTranslatorLocale($this->translator, $lang);

        try {
            $resultado = $this->usuarioService->EliminarImagenPerfil();

            if ($resultado['success']) {
                return $this->json(new ApiSimpleSuccessMessageResponse(
                    $this->translator->trans('usuario.message.imagen_eliminada', [], 'messages', $lang)
                ));
            }

            return $this->json(new ApiSimpleFailureResponse(
                $resultado['error'] ?? $this->translator->trans('usuario.error.eliminar_imagen', [], 'messages', $lang)
            ));
        } catch (\Exception $e) {
            // Si es error de formato JSON, retornar 400
            if (str_contains($e->getMessage(), 'Content-Type') || str_contains($e->getMessage(), 'Invalid JSON')) {
                return $this->json(new ApiSimpleFailureResponse($e->getMessage()), 400);
            }

            $this->loginService->writelogerror($e->getMessage());

            return $this->json(
                new ApiSimpleFailureResponse($this->translator->trans('message.exception', [], 'messages', $lang)),
                500
            );
        }
    }
}
