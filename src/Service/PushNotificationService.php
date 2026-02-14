<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Envía notificaciones push a dispositivos vía Firebase Cloud Messaging (FCM Legacy HTTP).
 * Usa firebase_server_key en .env (mismo parámetro que en SQMC).
 * @see https://firebase.google.com/docs/cloud-messaging/http-server-ref
 */
class PushNotificationService
{
    private const FCM_LEGACY_URL = 'https://fcm.googleapis.com/fcm/send';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $fcmServerKey = ''
    ) {
    }

    /**
     * Envía una notificación push a un dispositivo por su token FCM.
     *
     * @param string $token   Token FCM del dispositivo (push_token del usuario)
     * @param string $title   Título de la notificación
     * @param string $body    Cuerpo del mensaje
     * @param array  $data   Datos adicionales (todas las claves/valores se convierten a string para FCM)
     * @return bool True si se envió correctamente
     */
    public function sendToDevice(string $token, string $title, string $body, array $data = []): bool
    {
        if ($this->fcmServerKey === '' || $token === '') {
            $this->logger->debug('PushNotificationService: FCM key or token empty, skip send.');
            return false;
        }

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $this->stringifyData($data),
        ];

        try {
            $response = $this->httpClient->request('POST', self::FCM_LEGACY_URL, [
                'headers' => [
                    'Authorization' => 'key=' . $this->fcmServerKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->toArray(false);

            if ($statusCode >= 200 && $statusCode < 300) {
                if (isset($content['failure']) && (int) $content['failure'] > 0) {
                    $this->logger->warning('PushNotificationService: FCM reported failure', [
                        'response' => $content,
                        'token_preview' => substr($token, 0, 20) . '...',
                    ]);
                    return false;
                }
                return true;
            }

            $this->logger->warning('PushNotificationService: FCM non-2xx response', [
                'status' => $statusCode,
                'response' => $content,
            ]);
            return false;
        } catch (\Throwable $e) {
            $this->logger->error('PushNotificationService: exception sending push', [
                'message' => $e->getMessage(),
                'token_preview' => substr($token, 0, 20) . '...',
            ]);
            return false;
        }
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
