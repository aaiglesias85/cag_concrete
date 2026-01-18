<?php

namespace App\Controller\App;

use App\Controller\App\Traits\JsonRequestTrait;
use App\Utils\App\LoginService;
use App\Utils\App\UsuarioService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[OA\Tag(name: 'User', description: 'User profile management endpoints')]
class UsuarioController extends AbstractController
{
   use JsonRequestTrait;
   private LoginService $loginService;
   private UsuarioService $usuarioService;

   public function __construct(LoginService $loginService, UsuarioService $usuarioService)
   {
      $this->loginService = $loginService;
      $this->usuarioService = $usuarioService;
   }

   /**
    * cargarDatos Acción que carga los datos del usuario en la BD
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
   public function cargarDatos(): JsonResponse
   {
      try {
         $resultado = $this->usuarioService->CargarDatosUsuario();

         if ($resultado['success']) {
            $resultadoJson['success'] = true;
            $resultadoJson['usuario'] = $resultado['usuario'];
         } else {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $resultado['error'] ?? 'No se pudieron cargar los datos del usuario';
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = 'Ha ocurrido un error al procesar la solicitud';
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }

   /**
    * actualizarDatos Acción que actualiza los datos del usuario en la BD
    * Permite actualizar datos generales y opcionalmente cambiar la contraseña
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
   public function actualizarDatos(Request $request): JsonResponse
   {
      try {
         // Leer parámetros desde JSON body solamente
         $data = $this->getRequestData($request);
         
         $nombre = $data['nombre'] ?? null;
         $apellidos = $data['apellidos'] ?? null;
         $email = $data['email'] ?? null;
         $telefono = $data['telefono'] ?? null;

         // Contraseñas opcionales (solo si se quiere cambiar)
         $password_actual = $data['password_actual'] ?? '';
         $password = $data['password'] ?? '';

         // Actualizar datos (con o sin cambiar contraseña)
         $resultado = $this->usuarioService->ActualizarMisDatos(
            $nombre,
            $apellidos,
            $email,
            $telefono,
            $password_actual,
            $password
         );

         if ($resultado['success']) {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['message'] = "The operation was successful";
         } else {
            $resultadoJson['success'] = $resultado['success'];
            $resultadoJson['error'] = $resultado['error'];
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
    * salvarImagen Subir una imagen al servidor (base64)
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
   public function salvarImagen(Request $request): JsonResponse
   {
      try {
         $usuario = $this->getUser();
         if ($usuario == null) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = "No existe el usuario";
            return $this->json($resultadoJson);
         }

         // Leer parámetros desde JSON body o form data
         $data = $this->getRequestData($request);
         
         //por base 64
         $imagen = $data['imagen'] ?? null;

         if (empty($imagen)) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = "No se proporcionó una imagen";
            return $this->json($resultadoJson);
         }

         $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen));

         if ($data === false) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = "Error al decodificar la imagen";
            return $this->json($resultadoJson);
         }

         // Crear directorio si no existe
         $dir = 'uploads/usuario/';
         if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
         }

         // Generar nombre único para la imagen
         $foto = $this->usuarioService->generarCadenaAleatoria() . ".jpeg";
         $ruta_completa = $dir . $foto;

         file_put_contents($ruta_completa, $data);

         // Actualizar imagen del usuario en BD (a través del servicio)
         $resultado = $this->usuarioService->ActualizarImagenPerfil($foto);

         if ($resultado['success']) {
            $resultadoJson['success'] = true;
            $resultadoJson['imagen'] = $foto;
            $resultadoJson['message'] = "La operación se realizó correctamente";
         } else {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $resultado['error'] ?? 'Error al actualizar la imagen';
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = 'Ha ocurrido un error al procesar la solicitud';
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }

   /**
    * eliminarImagen Acción que elimina una imagen en la BD
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
   public function eliminarImagen(Request $request): JsonResponse
   {
      try {
         $resultado = $this->usuarioService->EliminarImagenPerfil();

         if ($resultado['success']) {
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = "La operación se realizó correctamente";
         } else {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $resultado['error'] ?? 'Error al eliminar la imagen';
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
    * sincronizar Sincroniza los datos offline del perfil del usuario
    */
   #[OA\Post(
      path: '/api/usuario/sincronizar',
      summary: 'Synchronize offline user profile data',
      description: 'Synchronizes offline user profile data that was saved locally when there was no connection. Requires authentication via Bearer token.',
      security: [['Bearer' => []]],
      requestBody: new OA\RequestBody(
         required: false,
         content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
               properties: [
                  new OA\Property(
                     property: 'profile_offline',
                     type: 'string',
                     description: 'JSON string containing offline profile data with fields: nombre, apellidos, email, telefono, passwordactual, password, imagen'
                  ),
               ]
            )
         )
      ),
      responses: [
         new OA\Response(
            response: 200,
            description: 'Data synchronized successfully',
            content: new OA\JsonContent(
               properties: [
                  new OA\Property(property: 'success', type: 'boolean', example: true),
                  new OA\Property(property: 'message', type: 'string', example: 'Data synchronized successfully'),
                  new OA\Property(
                     property: 'usuario',
                     type: 'object',
                     description: 'Updated user data'
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
                  new OA\Property(property: 'error', type: 'string', example: 'Error synchronizing data'),
               ]
            )
         ),
         new OA\Response(response: 401, description: 'Unauthorized - Invalid or missing token'),
         new OA\Response(response: 500, description: 'Internal server error'),
      ]
   )]
   public function sincronizar(Request $request): JsonResponse
   {
      try {
         // Leer datos desde form data
         $profile_offline_json = $request->request->get('profile_offline');
         
         if (empty($profile_offline_json)) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'No hay datos para sincronizar';
            return $this->json($resultadoJson, 400);
         }

         $profile_offline = json_decode($profile_offline_json, true);
         
         if (json_last_error() !== JSON_ERROR_NONE) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Error al decodificar los datos offline';
            return $this->json($resultadoJson, 400);
         }

         // Actualizar datos del perfil
         $nombre = $profile_offline['nombre'] ?? null;
         $apellidos = $profile_offline['apellidos'] ?? null;
         $email = $profile_offline['email'] ?? null;
         $telefono = $profile_offline['telefono'] ?? null;
         $password_actual = $profile_offline['passwordactual'] ?? '';
         $password = $profile_offline['password'] ?? '';

         $resultado = $this->usuarioService->ActualizarMisDatos(
            $nombre,
            $apellidos,
            $email,
            $telefono,
            $password_actual,
            $password
         );

         if (!$resultado['success']) {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = $resultado['error'] ?? 'Error al sincronizar los datos';
            return $this->json($resultadoJson, 400);
         }

         // Si hay imagen en los datos offline, sincronizarla también
         if (!empty($profile_offline['imagen'])) {
            $imagen = $profile_offline['imagen'];
            
            // Decodificar imagen base64
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagen));

            if ($data !== false) {
               // Crear directorio si no existe
               $dir = 'uploads/usuario/';
               if (!is_dir($dir)) {
                  mkdir($dir, 0755, true);
               }

               // Generar nombre único para la imagen
               $foto = $this->usuarioService->generarCadenaAleatoria() . ".jpeg";
               $ruta_completa = $dir . $foto;

               file_put_contents($ruta_completa, $data);

               // Actualizar imagen del usuario en BD
               $resultadoImagen = $this->usuarioService->ActualizarImagenPerfil($foto);
               
               if (!$resultadoImagen['success']) {
                  // Si falla la imagen, continuar pero registrar el error
                  $this->loginService->writelogerror('Error al sincronizar imagen: ' . ($resultadoImagen['error'] ?? 'Unknown error'));
               }
            }
         }

         // Obtener datos actualizados del usuario
         $resultadoUsuario = $this->usuarioService->CargarDatosUsuario();

         if ($resultadoUsuario['success']) {
            $resultadoJson['success'] = true;
            $resultadoJson['message'] = 'Los datos se sincronizaron correctamente';
            $resultadoJson['usuario'] = $resultadoUsuario['usuario'];
         } else {
            $resultadoJson['success'] = false;
            $resultadoJson['error'] = 'Error al cargar los datos actualizados';
         }

         return $this->json($resultadoJson);
      } catch (\Exception $e) {
         $resultadoJson['success'] = false;
         $resultadoJson['error'] = 'Ha ocurrido un error al procesar la solicitud';
         $this->loginService->writelogerror($e->getMessage());

         return $this->json($resultadoJson, 500);
      }
   }
}
