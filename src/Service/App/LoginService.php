<?php

namespace App\Service\App;

use App\Entity\AccessToken;
use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use App\Service\Admin\WidgetAccessService;
use App\Service\Base;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class LoginService extends Base
{
    private RequestStack $requestStack;

    private TranslatorInterface $translator;

    public function __construct(
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        ContainerBagInterface $containerBag,
        Security $security,
        LoggerInterface $logger,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        WidgetAccessService $widgetAccessService,
        RequestStack $requestStack,
        TranslatorInterface $translator,
    ) {
        parent::__construct($doctrine, $mailer, $containerBag, $security, $logger, $urlGenerator, $twig, $widgetAccessService);
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    private function setTranslatorLocale(string $locale): void
    {
        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }
    }

    /**
     * AutenticarLogin: Chequear el login y generar token.
     *
     * @param string      $email      Email
     * @param string      $pass       Password
     * @param string|null $player_id  Player ID para notificaciones push
     * @param string|null $push_token Token push
     * @param string|null $plataforma Plataforma (mobile/web)
     */
    public function AutenticarLogin($email, $pass, $player_id = null, $push_token = null, $plataforma = null, $lang = 'es'): array
    {
        $resultado = [];
        $em = $this->getDoctrine()->getManager();

        /** @var UsuarioRepository $usuarioRepo */
        $usuarioRepo = $this->getDoctrine()->getRepository(Usuario::class);
        $usuario = $usuarioRepo->BuscarUsuarioPorEmail($email);

        /** @var Usuario $usuario */
        if (null != $usuario && $this->VerificarPassword($pass, $usuario->getContrasenna())) {
            // Usuario activo y habilitado
            if (1 == $usuario->getHabilitado()) {
                // Actualizar datos de la app móvil
                if (null !== $player_id) {
                    $usuario->setPlayerId($player_id);
                }
                if (null !== $push_token) {
                    $usuario->setPushToken($push_token);
                }
                if (null !== $plataforma) {
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

                // Obtener permisos del usuario
                $permisos = $this->ListarPermisosDeUsuario($usuario->getUsuarioId());

                // Preparar datos del usuario para la respuesta (solo permisos, sin menú ni page config)
                $usuario_data = [
                    'usuario_id' => $usuario->getUsuarioId(),
                    'email' => $usuario->getEmail(),
                    'nombre' => $usuario->getNombre(),
                    'apellidos' => $usuario->getApellidos(),
                    'nombre_completo' => $usuario->getNombreCompleto(),
                    'telefono' => $usuario->getTelefono(),
                    'imagen' => $usuario->getImagen(),
                    'rol_id' => $usuario->getRol()?->getRolId(),
                    'rol' => $usuario->getRol()?->getNombre(),
                    'permisos' => $permisos,
                    'token' => $access_token, // Agregar token al usuario para compatibilidad
                    'preferred_lang' => $usuario->getPreferredLang(),
                    'chat' => $usuario->getChat(),
                ];

                // Codificar usuario en base64 para la app móvil
                $usuario_json = json_encode($usuario_data);
                $usuario_base64 = base64_encode($usuario_json);

                $resultado['success'] = true;
                $resultado['access_token'] = $access_token;
                $resultado['expires'] = $expires;
                $resultado['usuario'] = $usuario_base64;
            } else {
                // Usuario bloqueado
                $resultado['success'] = false;
                $this->setTranslatorLocale($lang);
                $resultado['error'] = $this->translator->trans('login.autenticar.usuario_bloqueado', [], 'messages', $lang);
            }
        } else {
            // Credenciales incorrectas
            $resultado['success'] = false;
            $this->setTranslatorLocale($lang);
            $resultado['error'] = $this->translator->trans('login.autenticar.error_login', [], 'messages', $lang);
        }

        return $resultado;
    }

    /**
     * CerrarSesion: Cerrar la sesión del usuario (eliminar token).
     */
    public function CerrarSesion(): array
    {
        $resultado = [];
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getUser();
        if (null != $usuario) {
            // Obtener token del header Authorization
            $token = $this->DevolverTokenUsuario();

            if ('' != $token) {
                $access_token = $this->getDoctrine()->getRepository(AccessToken::class)
                   ->findOneBy(['token' => $token]);

                if (null != $access_token) {
                    $em->remove($access_token);
                    $em->flush();
                    $resultado['success'] = true;
                } else {
                    // Token no encontrado en BD (puede estar ya eliminado o ser inválido)
                    $resultado['success'] = false;
                    $resultado['error'] = 'Token no encontrado o ya fue eliminado';
                }
            } else {
                $resultado['success'] = false;
                $resultado['error'] = 'No se proporcionó un token válido';
            }
        } else {
            $resultado['success'] = false;
            $resultado['error'] = 'No se pudo cerrar la sesión - usuario no autenticado';
        }

        return $resultado;
    }

    /**
     * Generar JWT token firmado.
     *
     * @param Usuario $usuario Usuario para incluir en el payload
     *
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
     * Devolver token del header Authorization.
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
        if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Escribir log de error.
     */
    public function writelogerror(string $message): void
    {
        $this->logger->error($message);
    }
}
