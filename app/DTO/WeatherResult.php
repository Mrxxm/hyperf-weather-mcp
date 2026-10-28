<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class WeatherResult
{
    public function __construct(
        public string $city,
        public string $country,
        public float $latitude,
        public float $longitude,
        public float $temperature,
        public float $apparentTemperature,
        public int $humidity,
        public float $precipitation,
        public float $windSpeed,
        public int $weatherCode,
        public string $condition,
        public string $observedAt,
        public string $timezone,
        public string $source,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        // REST 的 data 与 MCP 的 structuredContent 都调用此方法，确保两种入口输出一致。
        return [
            'location' => [
                'city' => $this->city,
                'country' => $this->country,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'current' => [
                'temperature' => $this->temperature,
                'apparent_temperature' => $this->apparentTemperature,
                'humidity' => $this->humidity,
                'precipitation' => $this->precipitation,
                'wind_speed' => $this->windSpeed,
                'weather_code' => $this->weatherCode,
                'condition' => $this->condition,
                'observed_at' => $this->observedAt,
            ],
            'units' => [
                'temperature' => '°C',
                'humidity' => '%',
                'precipitation' => 'mm',
                'wind_speed' => 'km/h',
            ],
            'timezone' => $this->timezone,
            'source' => $this->source,
        ];
    }

    public function summary(): string
    {
        // MCP content 使用精简自然语言，方便 Codex 直接理解并组织最终回答。
        return sprintf(
            '%s%s当前%s，气温 %.1f°C，体感 %.1f°C，湿度 %d%%，风速 %.1f km/h。',
            $this->country === '' ? '' : $this->country . ' ',
            $this->city,
            $this->condition,
            $this->temperature,
            $this->apparentTemperature,
            $this->humidity,
            $this->windSpeed,
        );
    }
}
