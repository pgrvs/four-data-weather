<?php

namespace App\Tests\Controller;

use App\Controller\WeatherController;
use App\Service\WeatherService;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(WeatherController::class)]
final class WeatherControllerTest extends WebTestCase
{
    public function testGetWeatherReturnsSuccess(): void
    {
        $client = static::createClient();

        $weatherServiceMock = $this->createMock(WeatherService::class);

        $weatherServiceMock->expects($this->once())
            ->method('getWeather')
            ->with(48.85, 2.35)
            ->willReturn([
                'latitude' => 48.85,
                'longitude' => 2.35,
                'current' => [
                    'temperature' => 15.0,
                    'weathercode' => 3
                ],
                'hourly' => [],
                'daily' => []
            ]);

        static::getContainer()->set(WeatherService::class, $weatherServiceMock);

        $client->request('GET', '/api/weather', [
            'latitude' => 48.85,
            'longitude' => 2.35
        ]);

        self::assertResponseIsSuccessful();

        self::assertResponseHeaderSame('content-type', 'application/json');

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($responseContent['success']);
        self::assertEquals('Météo récupérée avec succès', $responseContent['message']);
        self::assertEquals(15.0, $responseContent['data']['current']['temperature']);
    }

    public function testGetWeatherFailsWithoutCoordinates(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/weather');

        self::assertResponseStatusCodeSame(400);

        $responseContent = json_decode($client->getResponse()->getContent(), true);

        self::assertFalse($responseContent['success']);
        self::assertEquals('Latitude et longitude requises', $responseContent['message']);
    }
}
