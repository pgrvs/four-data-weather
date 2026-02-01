<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    /**
     * Retourne une réponse JSON de succès formatée.
     */
    public function success(mixed $data = null, string $message = 'Opération réussie', int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Retourne une réponse JSON d'erreur formatée.
     */
    public function error(string $message, int $status = 400, mixed $data = null): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
