<?php

namespace App\Controller;

use App\Service\ApiResponse;
use App\Service\CityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/city', name: 'api_city_')]
final class CityController extends AbstractController
{
    /**
     * GET /api/city/search?q=Paris
     * Endpoint pour l'autocomplétion des villes.
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request, CityService $cityService, ApiResponse $response): JsonResponse
    {
        $query = (string) $request->query->get('q', '');

        if (mb_strlen($query) < 2) {
            return $response->error('Veuillez saisir au moins 2 caractères', 400);
        }

        $cities = $cityService->searchCity($query);

        return $response->success($cities);
    }
}
