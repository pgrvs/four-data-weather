<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private const API_URL = 'https://api.open-meteo.com/v1/forecast';

    public function __construct(
        private HttpClientInterface $client
    ) {
    }

    public function getWeather(float $latitude, float $longitude): array
    {
        $response = $this->client->request('GET', self::API_URL, [
            'query' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timezone' => 'auto',
                'current' => 'temperature_2m,precipitation,precipitation_probability,apparent_temperature,wind_speed_10m,weathercode,is_day',
                'hourly' => 'temperature_2m,precipitation,precipitation_probability,apparent_temperature,wind_speed_10m',
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,weathercode',
            ],
        ]);

        $data = $response->toArray();

        return [
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone' => $data['timezone'],
            'elevation' => $data['elevation'],
            'units' => [
                'temp' => $data['daily_units']['temperature_2m_max'] ?? '°C',
                'speed' => $data['current_units']['wind_speed_10m'] ?? 'km/h',
                'rain' => $data['daily_units']['precipitation_sum'] ?? 'mm',
            ],
            'current' => [
                'time' => $data['current']['time'] ?? null,
                'temperature' => $data['current']['temperature_2m'] ?? null,
                // Ajout des champs manquants dans le mapping :
                'apparent_temperature' => $data['current']['apparent_temperature'] ?? null,
                'precipitation' => $data['current']['precipitation'] ?? 0,
                'probability' => $data['current']['precipitation_probability'] ?? 0,
                'wind_speed' => $data['current']['wind_speed_10m'] ?? 0,
                'weathercode' => $data['current']['weathercode'] ?? 0,
                'is_day' => $data['current']['is_day'] ?? 1, // 1 = Jour, 0 = Nuit
            ],
            'hourly' => $this->formatHourlyData($data['hourly'] ?? []),
            'daily' => $this->formatDailyData($data['daily'] ?? []),
        ];
    }

    /**
     * Filtre et format les données horaires pour ne garder que la journée en cours
     */
    private function formatHourlyData(array $hourlyData): array
    {
        if (empty($hourlyData['time'])) {
            return [];
        }

        $formatted = [];
        $currentDate = (new \DateTime())->format('Y-m-d');

        foreach ($hourlyData['time'] as $index => $time) {
            if (!str_starts_with($time, $currentDate)) {
                continue;
            }

            $formatted[] = [
                'time' => substr($time, 11, 5), // "14:00"
                'full_time' => $time,
                'temperature' => $hourlyData['temperature_2m'][$index] ?? null,
                'apparent_temperature' => $hourlyData['apparent_temperature'][$index] ?? null,
                'precipitation' => $hourlyData['precipitation'][$index] ?? 0,
                'probability' => $hourlyData['precipitation_probability'][$index] ?? 0,
                'wind_speed' => $hourlyData['wind_speed_10m'][$index] ?? 0,
            ];
        }

        return $formatted;
    }

    /**
     * Formate les données journalières (Max/Min par jour sur 7 jours)
     */
    private function formatDailyData(array $dailyData): array
    {
        if (empty($dailyData['time'])) {
            return [];
        }

        $formatted = [];

        foreach ($dailyData['time'] as $index => $date) {
            $formatted[] = [
                'date' => $date,
                'temperature_max' => $dailyData['temperature_2m_max'][$index] ?? null,
                'temperature_min' => $dailyData['temperature_2m_min'][$index] ?? null,
                'precipitation_sum' => $dailyData['precipitation_sum'][$index] ?? 0,
                'probability_max' => $dailyData['precipitation_probability_max'][$index] ?? 0,
                'weathercode' => $dailyData['weathercode'][$index] ?? 0,
            ];
        }

        return $formatted;
    }
}
