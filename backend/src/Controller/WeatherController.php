<?php

namespace App\Controller;

use App\Service\WeatherService;
use App\Service\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/weather', name: 'api_weather_')]
final class WeatherController extends AbstractController
{

    #[Route('', name: 'get', methods: ['GET'])]
    public function index(Request $request, WeatherService $weatherService, ApiResponse $response): JsonResponse
    {
        $lat = $request->query->get('latitude');
        $lon = $request->query->get('longitude');

        if (!$lat || !$lon) {
            return $response->error('Latitude et longitude requises', 400);
        }

        try {
            $data = $weatherService->getWeather((float)$lat, (float)$lon);
            return $response->success($data, 'Météo récupérée avec succès');
        } catch (\Exception $e) {
            return $response->error('Service météo indisponible : ' . $e->getMessage(), 503);
        }
    }
}
