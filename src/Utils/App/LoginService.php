<?php

namespace App\Utils\App;

use App\Entity\AccessToken;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Utils\Base;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginService extends Base
{
   private RequestStack $requestStack;

   public function __construct(
      \Symfony\Component\DependencyInjection\ContainerInterface $container,
      \Symfony\Component\Mailer\MailerInterface $mailer,
      \Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface $containerBag,
      \Symfony\Bundle\SecurityBundle\Security $security,
      \Psr\Log\LoggerInterface $logger,
      RequestStack $requestStack
   ) {
      parent::__construct($container, $mailer, $containerBag, $security, $logger);
      $this->requestStack = $requestStack;
   }
   /**
    * AutenticarLogin: Chequear el login y generar token
    *
    * @param string $email Email
    * @param string $pass Password
    * @param string|null $player_id Player ID para notificaciones push
    * @param string|null $push_token Token push
    * @param string|null $plataforma Plataforma (mobile/web)
    * @return array
    */
   public function AutenticarLogin($email, $pass, $player_id = null, $push_token = null, $plataforma = null): array
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      /** @var UsuarioRepository $usuarioRepo */
      $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
      $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);

      /** @var Usuario $usuario */
      if ($usuario != null && $this->VerificarPassword($pass, $usuario->getContrasenna())) {
         // Usuario activo y habilitado
         if ($usuario->getHabilitado() == 1) {
            // Actualizar datos de la app móvil
            if ($player_id !== null) {
               $usuario->setPlayerId($player_id);
            }
            if ($push_token !== null) {
               $usuario->setPushToken($push_token);
            }
            if ($plataforma !== null) {
               $usuario->setPlataforma($plataforma);
            }

            // Generar JWT token
            $expires = time() + 7 * 24 * 60 * 60; // 7 días
            $access_token = $this->generarAccessToken($usuario);

            // Guardar JWT token en BD
            $token = new AccessToken();
            $token->setToken($access_token);
            $token->setExpiresAt($expires);
            $token->setUser($usuario);

            $em->persist($token);
            $em->flush();

            // Preparar datos del usuario para la respuesta
            $usuario_data = [
               'usuario_id' => $usuario->getUsuarioId(),
               'email' => $usuario->getEmail(),
               'nombre' => $usuario->getNombre(),
               'apellidos' => $usuario->getApellidos(),
               'nombre_completo' => $usuario->getNombreCompleto(),
               'telefono' => $usuario->getTelefono(),
               'rol_id' => $usuario->getRol()?->getRolId(),
               'rol' => $usuario->getRol()?->getNombre(),
            ];

            $resultado['success'] = true;
            $resultado['access_token'] = $access_token;
            $resultado['expires'] = $expires;
            $resultado['usuario'] = $usuario_data;
         } else {
            // Usuario bloqueado
            $resultado['success'] = false;
            $resultado['error'] = "Su usuario ha sido bloqueado, por favor contacte con su administrador";
         }
      } else {
         // Credenciales incorrectas
         $resultado['success'] = false;
         $resultado['error'] = "Los datos de acceso son incorrectos";
      }

      return $resultado;
   }

   /**
    * CerrarSesion: Cerrar la sesión del usuario (eliminar token)
    *
    * @return array
    */
   public function CerrarSesion(): array
   {
      $resultado = array();
      $em = $this->getDoctrine()->getManager();

      $usuario = $this->getUser();
      if ($usuario != null) {
         // Obtener token del header Authorization
         $token = $this->DevolverTokenUsuario();

         if ($token != '') {
            $access_token = $this->getDoctrine()->getRepository(AccessToken::class)
               ->findOneBy(['token' => $token]);

            if ($access_token != null) {
               $em->remove($access_token);
               $em->flush();
               $resultado['success'] = true;
            } else {
               // Token no encontrado en BD (puede estar ya eliminado o ser inválido)
               $resultado['success'] = false;
               $resultado['error'] = "Token no encontrado o ya fue eliminado";
            }
         } else {
            $resultado['success'] = false;
            $resultado['error'] = "No se proporcionó un token válido";
         }
      } else {
         $resultado['success'] = false;
         $resultado['error'] = "No se pudo cerrar la sesión - usuario no autenticado";
      }

      return $resultado;
   }

   /**
    * Generar JWT token firmado
    *
    * @param Usuario $usuario Usuario para incluir en el payload
    * @return string JWT token firmado
    */
   private function generarAccessToken(Usuario $usuario): string
   {
      // Obtener secreto desde la configuración (APP_SECRET)
      $secret = $this->getParameter('kernel.secret');

      // Payload del JWT
      $issuedAt = time();
      $expirationTime = $issuedAt + (7 * 24 * 60 * 60); // 7 días

      $payload = [
         'user_id' => $usuario->getUsuarioId(),
         'email' => $usuario->getEmail(),
         'iat' => $issuedAt, // Issued at
         'exp' => $expirationTime, // Expiration time
      ];

      // Generar JWT firmado con HS256
      $jwt = JWT::encode($payload, $secret, 'HS256');

      return $jwt;
   }

   /**
    * Devolver token del header Authorization
    *
    * @return string
    */
   private function DevolverTokenUsuario(): string
   {
      $request = $this->requestStack->getCurrentRequest();

      if (!$request) {
         return '';
      }

      $authorizationHeader = $request->headers->get('Authorization');

      if (!$authorizationHeader) {
         return '';
      }

      // Extract token from "Bearer <token>" format
      $matches = [];
      if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches) && isset($matches[1])) {
         return $matches[1];
      }

      return '';
   }

   /**
    * Escribir log de error
    */
   public function writelogerror(string $message): void
   {
      $this->logger->error($message);
   }
}
