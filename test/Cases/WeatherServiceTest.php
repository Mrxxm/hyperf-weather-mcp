<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use App\Client\WeatherClientInterface;
use App\Service\WeatherService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** @internal */
class WeatherServiceTest extends TestCase
{
    public function testQueryMapsWeatherResult(): void
    {
        $service = new WeatherService($this->fakeClient());

        $result = $service->query('上海');

        self::assertSame('上海', $result->city);
        self::assertSame('多云', $result->condition);
        self::assertSame(26.5, $result->temperature);
        self::assertSame('wttr.in', $result->toArray()['source']);
    }

    public function testQueryRejectsEmptyCity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new WeatherService($this->fakeClient()))->query('  ');
    }

    private function fakeClient(): WeatherClientInterface
    {
        return new class implements WeatherClientInterface {
            public function query(string $city): array
            {
                return [
                    'location' => [
                        'name' => $city,
                        'country' => '中国',
                        'latitude' => 31.23,
                        'longitude' => 121.47,
                    ],
                    'current' => [
                        'temperature' => 26.5,
                        'apparent_temperature' => 27.1,
                        'humidity' => 61,
                        'precipitation' => 0.0,
                        'weather_code' => 116,
                        'wind_speed' => 12.3,
                        'time' => '2026-10-28T12:00',
                        'timezone' => 'Asia/Shanghai',
                    ],
                    'source' => 'wttr.in',
                ];
            }
        };
    }
}
