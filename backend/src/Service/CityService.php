<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CityService
{
    private const API_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    /**
     * Recherche une liste de villes correspondant au nom donné
     */
    public function searchCity(string $name): array
    {
        try {
            $response = $this->client->request('GET', self::API_URL, [
                'query' => [
                    'name' => $name,
                    'count' => 5,
                    'language' => 'fr',
                    'format' => 'json'
                ]
            ]);

            $data = $response->toArray();

            return $data['results'] ?? [];

        } catch (TransportExceptionInterface $e) {
            return [];
        }
    }
}
