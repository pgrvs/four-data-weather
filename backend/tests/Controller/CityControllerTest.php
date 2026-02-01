<?php

namespace App\Tests\Controller;

use App\Controller\CityController;
use App\Service\CityService;
use App\Service\ApiResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(ApiResponse::class)]
#[CoversClass(CityController::class)]
final class CityControllerTest extends WebTestCase
{
    public function testSearchReturnsSuccess(): void
    {
        $client = static::createClient();

        $cityServiceMock = $this->createMock(CityService::class);

        $cityServiceMock->expects($this->once())
        ->method('searchCity')
            ->with('Paris')
            ->willReturn([
                [
                    'id' => 1,
                    'name' => 'Paris',
                    'country' => 'France',
                    'latitude' => 48.85,
                    'longitude' => 2.35
                ]
            ]);

        static::getContainer()->set(CityService::class, $cityServiceMock);

        $client->request('GET', '/api/city/search?q=Paris');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($response['success']);
        self::assertEquals(200, $response['code']);
        self::assertCount(1, $response['data']);
        self::assertEquals('Paris', $response['data'][0]['name']);
    }

    public function testSearchFailsIfQueryTooShort(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/city/search?q=a');

        self::assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertFalse($response['success']);
        self::assertEquals(400, $response['code']);
        self::assertStringContainsString('au moins 2 caractères', $response['message']);
    }

    public function testSearchReturnsEmptyArrayIfNotFound(): void
    {
        $client = static::createClient();

        $cityServiceMock = $this->createMock(CityService::class);
        $cityServiceMock->method('searchCity')->willReturn([]);

        static::getContainer()->set(CityService::class, $cityServiceMock);

        $client->request('GET', '/api/city/search?q=Introuvable');

        self::assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($response['success']);
        self::assertEmpty($response['data']);
    }
}
