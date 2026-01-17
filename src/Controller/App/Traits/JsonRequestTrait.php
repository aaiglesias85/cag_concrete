<?php

namespace App\Controller\App\Traits;

use Symfony\Component\HttpFoundation\Request;

trait JsonRequestTrait
{
   /**
    * Helper method to get request data from JSON body only
    * @throws \Exception if content is not JSON
    */
   private function getRequestData(Request $request): array
   {
      // Verificar que el Content-Type sea application/json
      $contentType = $request->headers->get('Content-Type', '');
      if (!str_contains($contentType, 'application/json')) {
         throw new \Exception('Content-Type must be application/json');
      }

      // Leer del body JSON
      $content = $request->getContent();
      if (empty($content)) {
         return [];
      }

      $data = json_decode($content, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
         throw new \Exception('Invalid JSON format: ' . json_last_error_msg());
      }

      return is_array($data) ? $data : [];
   }
}
