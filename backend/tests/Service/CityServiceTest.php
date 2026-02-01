<?php

namespace App\Tests\Service;

use App\Service\CityService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(CityService::class)]
final class CityServiceTest extends TestCase
{
    public function testSearchCityReturnsData(): void
    {
        $mockApiResponse = [
            'results' => [
                [
                    "id" => 2988507,
                    "name" => "Paris",
                    "country" => "France",
                    "latitude" => 48.85341,
                    "longitude" => 2.3488,
                ],
                [
                    "id" => 4717560,
                    "name" => "Paris",
                    "country" => "États Unis",
                    "latitude" => 33.66094,
                    "longitude" => -95.55551,
                ]
            ]
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')
            ->willReturn($mockApiResponse);

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
        ->method('request')
            ->with(
                'GET',
                'https://geocoding-api.open-meteo.com/v1/search',
                [
                    'query' => [
                        'name' => 'Paris',
                        'count' => 5,
                        'language' => 'fr',
                        'format' => 'json'
                    ]
                ]
            )
            ->willReturn($responseMock);

        $cityService = new CityService($clientMock);

        $results = $cityService->searchCity('Paris');

        $this->assertCount(2, $results);
        $this->assertEquals('Paris', $results[0]['name']);
        $this->assertEquals('France', $results[0]['country']);
    }

    public function testSearchCityReturnsEmptyIfNoResult(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn(['results' => []]);

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->method('request')->willReturn($responseMock);

        $cityService = new CityService($clientMock);
        $results = $cityService->searchCity('Introuvable');

        $this->assertEmpty($results);
    }

    public function testSearchCityReturnsEmptyOnApiCrash(): void
    {
        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $cityService = new CityService($clientMock);

        $results = $cityService->searchCity('Paris');

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }
}
