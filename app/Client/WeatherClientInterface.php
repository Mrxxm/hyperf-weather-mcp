<?php

declare(strict_types=1);

namespace App\Client;

interface WeatherClientInterface
{
    /**
     * @return array{
     *     location: array{name: string, country: string, latitude: float, longitude: float},
     *     current: array{
     *         temperature: float,
     *         apparent_temperature: float,
     *         humidity: int,
     *         precipitation: float,
     *         weather_code: int,
     *         wind_speed: float,
     *         time: string,
     *         timezone: string
     *     },
     *     source: string
     * }
     */
    public function query(string $city): array;
}
