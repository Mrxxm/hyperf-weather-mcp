<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use App\Client\WeatherClientInterface;
use App\Mcp\Tool\GetWeatherTool;
use App\Service\WeatherService;
use PHPUnit\Framework\TestCase;

/** @internal */
class GetWeatherToolTest extends TestCase
{
    public function testToolReturnsTextAndStructuredContent(): void
    {
        $client = new class implements WeatherClientInterface {
            public function query(string $city): array
            {
                return [
                    'location' => [
                        'name' => $city,
                        'country' => '中国',
                        'latitude' => 39.9,
                        'longitude' => 116.4,
                    ],
                    'current' => [
                        'temperature' => 18.2,
                        'apparent_temperature' => 17.8,
                        'humidity' => 45,
                        'precipitation' => 0.0,
                        'weather_code' => 113,
                        'wind_speed' => 8.0,
                        'time' => '2026-10-28T12:00',
                        'timezone' => 'Asia/Shanghai',
                    ],
                    'source' => 'wttr.in',
                ];
            }
        };

        $result = (new GetWeatherTool(new WeatherService($client)))->getWeather('北京');

        self::assertFalse($result->isError);
        self::assertStringContainsString('北京当前晴', $result->content[0]->text);
        self::assertSame('wttr.in', $result->structuredContent['source']);
    }
}
