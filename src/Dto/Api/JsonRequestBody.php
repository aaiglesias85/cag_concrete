<?php

namespace App\Dto\Api;

use Symfony\Component\HttpFoundation\Request;

/**
 * Decodifica el cuerpo como JSON: exige Content-Type application/json, body vacío → [],
 * json_decode con error → \Exception con el mensaje de json_last_error_msg().
 */
final class JsonRequestBody
{
    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public static function decodeAssociative(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            throw new \Exception('Content-Type must be application/json');
        }

        $content = $request->getContent();
        if ('' === $content) {
            return [];
        }

        $data = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Invalid JSON format: '.json_last_error_msg());
        }

        return \is_array($data) ? $data : [];
    }
}
