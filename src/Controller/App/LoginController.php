<?php

namespace App\Controller\App;

use App\Controller\App\Traits\JsonRequestTrait;
use App\Utils\App\LoginService;
use App\Utils\Admin\UsuarioService as AdminUsuarioService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

#[OA\Tag(name: 'Login', description: 'Authentication endpoints for mobile application')]
class LoginController extends AbstractController
{
   use JsonRequestTrait;
   private LoginService $loginService;
   private AdminUsuarioService $adminUsuarioService;
   private TranslatorInterface $translator;

   public function __construct(LoginService $loginService, AdminUsuarioService $adminUsuarioService, TranslatorInterface $translator)
   {
      $this->loginService = $loginService;
      $this->adminUsuarioService = $adminUsuarioService;
      $this->translator = $translator;
   }

   /**
    * autenticar Acción para el chequear el login y generar token
    */
   #[OA\Post(
      path: '/api/{lang}/login/autenticar',
      summary: 'Authenticate user and get JWT token',
      description: 'Authenticates a user by email and password. Returns a JWT token that must be used in subsequent requests. This endpoint is public and does not require authentication. The {lang} parameter (es|en) determines the language of error messages.',
      parameters: [
         new OA\Parameter(
            name: 'lang',
            in: 'path',
            required: true,
            description: 'Language code (es for Spanish, en for English)',
            schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
         ),
      ],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
               new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com', description: 'User email address'),
               new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123', description: 'User password'),
               new OA\Property(property: 'player_id', type: 'string', nullable: true, example: 'player123', description: 'Device ID for push notifications (optional)'),
               new OA\Property(property: 'push_token', type: 'string', nullable: true, example: 'push_token_abc123', description: 'Push notification token (optional)'),
               new OA\Property(property: 'plataforma', type: 'string', nullable: true, example: 'ios', enum: ['ios', 'android', 'web'], description: 'Device platform (optional)'),
            ]
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Authentication successful',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: true),
                  new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...', description: 'JWT token for authentication in subsequent requests'),
                  new OA\Property(property: 'expires', type: 'integer', example: 1735689600, description: 'Token expiration timestamp (Unix timestamp)'),
                  new OA\Property(
                     property: 'usuario',
                     type: 'string',
                     description: 'User data encoded in base64 format',
                     example: 'eyJ1c3VhcmlvX2lkIjoxLCJlbWFpbCI6InVzZXJAZXhhbXBsZS5jb20iLCJub21icmUiOiJKb2huIn0='
                  ),
               ]
            )
         ),
         new OA\Response(
            response: 401,
            description: 'Authentication error - Invalid credentials or blocked user',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: false),
                  new OA\Property(property: 'error', type: 'string', example: 'Invalid login credentials'),
                  new OA\Property(property: 'intento_login', type: 'integer', nullable: true, example: 3, description: 'Number of failed login attempts'),
               ]
            )
         ),
         new OA\Response(response: 500, description: 'Internal server error'),
      ]
   )]
   public function autenticar(Request $request, string $lang = 'es'): JsonResponse
   {
      try {
         // Establecer locale para traducciones
         $request->setLocale($lang);
         $this->translator->setLocale($lang);

         // Leer parámetros desde JSON solamente
         $data = $this->getRequestData($request);

         $email = $data['email'] ?? null;
         $pass = $data['password'] ?? null;

         // Para la app móvil
         $player_id = $data['player_id'] ?? null;
         $push_token = $data['push_token'] ?? null;
         $plataforma = $data['plataforma'] ?? null;
         $resultado = $this->loginService->AutenticarLogin($email, $pass, $player_id, $push_token, $plataforma, $lang);

         if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['access_token'] = $resultado['access_token'];
            $resultadoJson['expires'] = $resultado['expires'];
            $resultadoJson['usuario'] = $resultado['usuario'];
         } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];

            if (isset($resultado['intento_login'])) {
               $resultadoJson['intento_login'] = $resultado['intento_login'];
            }
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;

         // Si es error de formato JSON, retornar 400
         if (str_contains($e->getMessage(), 'Content-Type') || str_contains($e->getMessage(), 'Invalid JSON')) {
            $resultadoJson['error'] = $e->getMessage();
            return $this->json($resultadoJson, 400);
         }

         $this->translator->setLocale($lang);
         $resultadoJson['error'] = $this->translator->trans('message.exception', [], 'messages', $lang);
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }

   /**
    * cerrarSesion Acción para cerrar la sesión del usuario (eliminar token)
    */
   #[OA\Post(
      path: '/api/{lang}/login/cerrar-sesion',
      summary: 'Logout user',
      description: 'Invalidates the current JWT token of the user, closing their session. Requires authentication via Bearer token. The {lang} parameter (es|en) determines the language of error messages.',
      parameters: [
         new OA\Parameter(
            name: 'lang',
            in: 'path',
            required: true,
            description: 'Language code (es for Spanish, en for English)',
            schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
         ),
      ],
      security: [['Bearer' => []]],
      responses: [
         new OA\Response(
            response: 200,
            description: 'Session closed successfully',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: true),
                  new OA\Property(property: 'message', type: 'string', example: 'Session closed successfully'),
               ]
            )
         ),
         new OA\Response(
            response: 400,
            description: 'Error closing session',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: false),
                  new OA\Property(property: 'error', type: 'string', example: 'Error closing session'),
               ]
            )
         ),
         new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
         new OA\Response(response: 500, description: 'Internal server error'),
      ]
   )]
   public function cerrarSesion(Request $request, string $lang = 'es'): JsonResponse
   {
      try {
         // Establecer locale para traducciones
         $request->setLocale($lang);
         $this->translator->setLocale($lang);

         $resultado = $this->loginService->CerrarSesion();

         if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = $this->translator->trans('message.success', [], 'messages', $lang);
         } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $this->translator->setLocale($lang);
         $resultadoJson['error'] = $this->translator->trans('login.error.logout', [], 'messages', $lang);
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }

   /**
    * olvidoContrasenna Acción para recuperar la contraseña de un usuario
    */
   #[OA\Post(
      path: '/api/{lang}/login/olvido-Contrasenna',
      summary: 'Recover forgotten password',
      description: 'Sends a password recovery email to the user. This endpoint is public and does not require authentication. Receives email in JSON body. The {lang} parameter (es|en) determines the language of error messages.',
      parameters: [
         new OA\Parameter(
            name: 'lang',
            in: 'path',
            required: true,
            description: 'Language code (es for Spanish, en for English)',
            schema: new OA\Schema(type: 'string', enum: ['es', 'en'])
         ),
      ],
      requestBody: new OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
            required: ['email'],
            properties: [
               new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com', description: 'User email address'),
            ]
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Password recovery email sent successfully',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: true),
                  new OA\Property(property: 'message', type: 'string', example: 'The password recovery process has been started successfully, in a few moments you will receive an email to the address entered'),
               ]
            )
         ),
         new OA\Response(
            response: 400,
            description: 'Error processing request',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: false),
                  new OA\Property(property: 'error', type: 'string', example: 'Invalid JSON format or missing email'),
               ]
            )
         ),
         new OA\Response(response: 500, description: 'Internal server error'),
      ]
   )]
   public function olvidoContrasenna(Request $request, string $lang = 'es'): JsonResponse
   {
      try {
         // Establecer locale para traducciones
         $request->setLocale($lang);
         $this->translator->setLocale($lang);

         // Leer parámetros desde JSON solamente
         $data = $this->getRequestData($request);

         $email = $data['email'] ?? null;

         if (empty($email)) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $this->translator->trans('error.email_incorrecto', [], 'messages', $lang);
            return $this->json($resultadoJson, 400);
         }

         // Procesar recuperación de contraseña (siempre devuelve éxito para evitar descubrir emails existentes)
         $this->adminUsuarioService->RecuperarContrasenna($email);

         // Siempre devolver éxito independientemente de si el email existe o no
         // Esto previene que usuarios maliciosos descubran emails registrados
         $resultadoJson['success'] = true;
         $resultadoJson['message'] = $this->translator->trans('login.message.forgot_pass', [], 'messages', $lang);

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;

         // Si es error de formato JSON, retornar 400
         if (str_contains($e->getMessage(), 'Content-Type') || str_contains($e->getMessage(), 'Invalid JSON')) {
            $resultadoJson['error'] = $e->getMessage();
            return $this->json($resultadoJson, 400);
         }

         $this->translator->setLocale($lang);
         $resultadoJson['error'] = $this->translator->trans('message.exception', [], 'messages', $lang);
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }
}
