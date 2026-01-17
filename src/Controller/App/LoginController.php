<?php

namespace App\Controller\App;

use App\Controller\App\Traits\JsonRequestTrait;
use App\Utils\App\LoginService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[OA\Tag(name: 'Login', description: 'Authentication endpoints for mobile application')]
class LoginController extends AbstractController
{
   use JsonRequestTrait;
   private LoginService $loginService;

   public function __construct(LoginService $loginService)
   {
      $this->loginService = $loginService;
   }

   /**
    * autenticar Acción para el chequear el login y generar token
    */
   #[OA\Post(
      path: '/api/login/autenticar',
      summary: 'Authenticate user and get JWT token',
      description: 'Authenticates a user by email and password. Returns a JWT token that must be used in subsequent requests. This endpoint is public and does not require authentication.',
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
                     type: 'object',
                     properties: [
                        new OA\Property(property: 'usuario_id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        new OA\Property(property: 'nombre', type: 'string', example: 'John'),
                        new OA\Property(property: 'apellidos', type: 'string', example: 'Doe'),
                        new OA\Property(property: 'nombre_completo', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'telefono', type: 'string', nullable: true, example: '+1234567890'),
                        new OA\Property(property: 'rol_id', type: 'integer', nullable: true, example: 2),
                        new OA\Property(property: 'rol', type: 'string', nullable: true, example: 'User'),
                     ]
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
   public function autenticar(Request $request): JsonResponse
   {
      try {
         // Leer parámetros desde JSON body solamente
         $data = $this->getRequestData($request);

         $email = $data['email'] ?? null;
         $pass = $data['password'] ?? null;

         // Para la app móvil
         $player_id = $data['player_id'] ?? null;
         $push_token = $data['push_token'] ?? null;
         $plataforma = $data['plataforma'] ?? null;
         $resultado = $this->loginService->AutenticarLogin($email, $pass, $player_id, $push_token, $plataforma);

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

         $resultadoJson['error'] = 'An error occurred while processing the request';
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }

   /**
    * cerrarSesion Acción para cerrar la sesión del usuario (eliminar token)
    */
   #[OA\Post(
      path: '/api/login/cerrar-sesion',
      summary: 'Logout user',
      description: 'Invalidates the current JWT token of the user, closing their session. Requires authentication via Bearer token.',
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
   public function cerrarSesion(): JsonResponse
   {
      try {
         $resultado = $this->loginService->CerrarSesion();

         if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = 'Sesión cerrada exitosamente';
         } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = 'Ha ocurrido un error al cerrar la sesión';
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }
}
