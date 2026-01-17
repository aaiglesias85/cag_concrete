<?php

namespace App\Security;

use App\Entity\AccessToken;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
   private EntityManagerInterface $em;
   private ContainerBagInterface $containerBag;

   public function __construct(EntityManagerInterface $em, ContainerBagInterface $containerBag)
   {
      $this->em = $em;
      $this->containerBag = $containerBag;
   }

   /**
    * Called on every request to decide if this authenticator should be
    * used for the request. Returning `false` will cause this authenticator
    * to be skipped.
    */
   public function supports(Request $request): ?bool
   {
      return $request->headers->has('Authorization')
         && str_starts_with($request->headers->get('Authorization', ''), 'Bearer ');
   }

   /**
    * Creates a passport for the current request.
    */
   public function authenticate(Request $request): Passport
   {
      $token = $this->getTokenFromRequest($request);

      if (!$token) {
         throw new AuthenticationException('Token not found');
      }

      return new SelfValidatingPassport(
         new UserBadge($token, function (string $tokenIdentifier) {
            return $this->getUserFromToken($tokenIdentifier);
         })
      );
   }

   /**
    * Extract token from request header Authorization: Bearer <token>
    */
   private function getTokenFromRequest(Request $request): ?string
   {
      $authorizationHeader = $request->headers->get('Authorization');

      if (!$authorizationHeader) {
         return null;
      }

      // Extract token from "Bearer <token>" format
      $matches = [];
      if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches) && isset($matches[1])) {
         return $matches[1];
      }

      return null;
   }

   /**
    * Get user from JWT token
    */
   private function getUserFromToken(string $jwtToken): ?\App\Entity\Usuario
   {
      // Obtener secreto desde la configuración (APP_SECRET)
      $secret = $this->containerBag->get('kernel.secret');

      try {
         // Decodificar y verificar el JWT
         $decoded = JWT::decode($jwtToken, new Key($secret, 'HS256'));

         // Verificar que el token existe en la BD
         $accessToken = $this->em->getRepository(AccessToken::class)
            ->findOneBy(['token' => $jwtToken]);

         if (!$accessToken) {
            return null;
         }

         // Verificar token expiration desde BD (doble verificación)
         $time = time();
         $expires = $accessToken->getExpiresAt();

         if ($time > $expires) {
            return null;
         }

         // Verificar que el user_id del JWT coincide con el token en BD
         if (isset($decoded->user_id) && $decoded->user_id !== $accessToken->getUser()->getUsuarioId()) {
            return null;
         }

         return $accessToken->getUser();

      } catch (ExpiredException $e) {
         // JWT expirado
         return null;
      } catch (SignatureInvalidException $e) {
         // Firma JWT inválida
         return null;
      } catch (\Exception $e) {
         // Cualquier otro error en la decodificación del JWT
         return null;
      }
   }

   public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
   {
      // On success, let the request continue
      return null;
   }

   public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
   {
      $data = [
         'success' => false,
         'error' => 'Debes hacer login',
         'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
      ];

      return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
   }
}
