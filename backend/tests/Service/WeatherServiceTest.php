<?php

namespace App\Tests\Service;

use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(WeatherService::class)]
final class WeatherServiceTest extends TestCase
{
    public function testGetWeatherReturnsFormattedData(): void
    {
        $todayDate = (new \DateTime())->format('Y-m-d');
        $tomorrowDate = (new \DateTime('+1 day'))->format('Y-m-d');

        $mockApiResponse = [
            'latitude' => 45.0,
            'longitude' => 6.0,
            'timezone' => 'Europe/Paris',
            'elevation' => 200,
            'current_units' => ['wind_speed_10m' => 'km/h'],
            'daily_units' => ['temperature_2m_max' => '°C', 'precipitation_sum' => 'mm'],
            'current' => [
                'time' => $todayDate . 'T12:00',
                'temperature_2m' => 15.5,
                'apparent_temperature' => 14.0,
                'precipitation' => 0.0,
                'precipitation_probability' => 10,
                'wind_speed_10m' => 12.0,
                'weathercode' => 3,
                'is_day' => 1,
            ],
            'hourly' => [
                'time' => [
                    $todayDate . 'T10:00',
                    $todayDate . 'T11:00',
                    $tomorrowDate . 'T00:00',
                ],
                'temperature_2m' => [10.0, 11.0, 5.0],
                'apparent_temperature' => [9.0, 10.0, 4.0],
                'precipitation' => [0, 0, 1],
                'precipitation_probability' => [0, 5, 50],
                'wind_speed_10m' => [10, 12, 20],
            ],
            'daily' => [
                'time' => [$todayDate, $tomorrowDate],
                'temperature_2m_max' => [15.0, 16.0],
                'temperature_2m_min' => [5.0, 6.0],
                'precipitation_sum' => [0.0, 2.0],
                'precipitation_probability_max' => [10, 40],
                'weathercode' => [1, 3],
            ]
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn($mockApiResponse);

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.open-meteo.com/v1/forecast',
                $this->callback(function ($options) {
                    return isset($options['query']['current'])
                        && isset($options['query']['hourly'])
                        && isset($options['query']['daily']);
                })
            )
            ->willReturn($responseMock);

        $service = new WeatherService($clientMock);
        $result = $service->getWeather(45.0, 6.0);

        $this->assertEquals(45.0, $result['latitude']);
        $this->assertEquals(15.5, $result['current']['temperature']);
        $this->assertEquals('km/h', $result['units']['speed']);

        $this->assertCount(2, $result['hourly']);

        $this->assertEquals('10:00', $result['hourly'][0]['time']);
        $this->assertEquals('11:00', $result['hourly'][1]['time']);

        $this->assertEquals(10.0, $result['hourly'][0]['temperature']);

        $this->assertCount(2, $result['daily']);
        $this->assertEquals($todayDate, $result['daily'][0]['date']);
        $this->assertEquals(15.0, $result['daily'][0]['temperature_max']);
    }

    public function testGetWeatherHandlesEmptyArrays(): void
    {
        $mockEmptyResponse = [
            'latitude' => 0, 'longitude' => 0, 'timezone' => 'UTC', 'elevation' => 0,
            'current_units' => [], 'daily_units' => [],
            'current' => [],
            'hourly' => [],
            'daily' => []
        ];

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('toArray')->willReturn($mockEmptyResponse);

        $clientMock = $this->createMock(HttpClientInterface::class);
        $clientMock->method('request')->willReturn($responseMock);

        $service = new WeatherService($clientMock);
        $result = $service->getWeather(0, 0);

        $this->assertEmpty($result['hourly']);
        $this->assertEmpty($result['daily']);
    }
}
