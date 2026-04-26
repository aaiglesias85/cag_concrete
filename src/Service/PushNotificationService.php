<?php

namespace App\Service;

use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Envía notificaciones push a dispositivos vía Firebase Cloud Messaging (FCM HTTP v1).
 * Usa cuenta de servicio (JSON) y OAuth2. La API heredada (Legacy) está obsoleta desde 2024.
 *
 * @see https://firebase.google.com/docs/cloud-messaging/send-message#rest
 * @see https://firebase.google.com/docs/cloud-messaging/auth-server
 */
class PushNotificationService
{
    private const FCM_V1_SEND_URL = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    private const OAUTH2_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /** Cache del access token (válido ~1h). Clave: project_id. */
    private static ?array $tokenCache = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $projectId = '',
        private string $serviceAccountJsonPath = '',
        private string $projectDir = '',
    ) {
    }

    /**
     * Envía una notificación push a un dispositivo por su token FCM.
     *
     * @param string $token Token FCM del dispositivo (push_token del usuario)
     * @param string $title Título de la notificación
     * @param string $body  Cuerpo del mensaje
     * @param array  $data  Datos adicionales (todas las claves/valores se convierten a string para FCM)
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = []): bool
    {
        if ('' === $this->projectId || '' === $this->serviceAccountJsonPath || '' === $token) {
            $this->logger->debug('PushNotificationService: FCM project_id, service account path or token empty, skip send.');

            return false;
        }

        $accessToken = $this->getAccessToken();
        if ('' === $accessToken) {
            return false;
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->stringifyData($data),
            ],
        ];

        $url = sprintf(self::FCM_V1_SEND_URL, $this->projectId);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            $content = $response->toArray(false);
            $this->logger->warning('PushNotificationService: FCM v1 non-2xx response', [
                'status' => $statusCode,
                'response' => $content,
                'token_preview' => substr($token, 0, 20).'...',
            ]);

            return false;
        } catch (\Throwable $e) {
            $this->logger->error('PushNotificationService: exception sending push', [
                'message' => $e->getMessage(),
                'token_preview' => substr($token, 0, 20).'...',
            ]);

            return false;
        }
    }

    /**
     * Obtiene un access token OAuth2 para FCM usando la cuenta de servicio (JWT).
     * Cache en memoria por request para no repetir llamadas.
     */
    private function getAccessToken(): string
    {
        $cacheKey = $this->projectId;
        if (null !== self::$tokenCache && self::$tokenCache['key'] === $cacheKey && self::$tokenCache['expires_at'] > time()) {
            return self::$tokenCache['token'];
        }

        $credentials = $this->loadServiceAccount();
        if (null === $credentials) {
            return '';
        }

        $now = time();
        $jwtPayload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => self::OAUTH2_TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => self::FCM_SCOPE,
        ];

        try {
            $privateKey = $credentials['private_key'];
            if (str_contains($privateKey, '\\n')) {
                $privateKey = str_replace('\\n', "\n", $privateKey);
            }
            $jwt = JWT::encode($jwtPayload, $privateKey, 'RS256');
        } catch (\Throwable $e) {
            $this->logger->error('PushNotificationService: JWT encode failed', ['message' => $e->getMessage()]);

            return '';
        }

        try {
            $response = $this->httpClient->request('POST', self::OAUTH2_TOKEN_URL, [
                'body' => http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ]),
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            $content = $response->toArray();
        } catch (\Throwable $e) {
            $this->logger->error('PushNotificationService: OAuth2 token request failed', ['message' => $e->getMessage()]);

            return '';
        }

        if (!isset($content['access_token'])) {
            $this->logger->warning('PushNotificationService: no access_token in OAuth2 response', ['response' => $content]);

            return '';
        }

        $expiresIn = (int) ($content['expires_in'] ?? 3600);
        self::$tokenCache = [
            'key' => $cacheKey,
            'token' => $content['access_token'],
            'expires_at' => time() + $expiresIn - 300, // refrescar 5 min antes
        ];

        return $content['access_token'];
    }

    /**
     * Carga el JSON de la cuenta de servicio (client_email, private_key).
     *
     * @return array{client_email: string, private_key: string}|null
     */
    private function loadServiceAccount(): ?array
    {
        $path = $this->serviceAccountJsonPath;
        if ('' === $path) {
            return null;
        }
        if (!str_starts_with($path, '/') && '' !== $this->projectDir) {
            $path = rtrim($this->projectDir, \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.ltrim($path, \DIRECTORY_SEPARATOR);
        }
        if (!is_readable($path)) {
            $this->logger->warning('PushNotificationService: service account file not readable', ['path' => $path]);

            return null;
        }

        $json = file_get_contents($path);
        if (false === $json) {
            $this->logger->warning('PushNotificationService: could not read service account file');

            return null;
        }

        $data = json_decode($json, true);
        if (!isset($data['client_email'], $data['private_key'])) {
            $this->logger->warning('PushNotificationService: service account JSON missing client_email or private_key');

            return null;
        }

        return [
            'client_email' => $data['client_email'],
            'private_key' => $data['private_key'],
        ];
    }

    /**
     * FCM data payload solo acepta valores string.
     */
    private function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            $out[(string) $k] = (string) $v;
        }

        return $out;
    }
}
