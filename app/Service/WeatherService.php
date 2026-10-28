<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\WeatherClientInterface;
use App\DTO\WeatherResult;
use InvalidArgumentException;

class WeatherService
{
    public function __construct(private readonly WeatherClientInterface $client)
    {
    }

    public function query(string $city): WeatherResult
    {
        // 这是 REST 和 MCP 的公共业务边界，参数规则只维护一份。
        $city = trim($city);
        if ($city === '' || mb_strlen($city) > 100) {
            throw new InvalidArgumentException('city 必须是 1 到 100 个字符的城市名称');
        }

        // 接口绑定在 config/autoload/dependencies.php；当前实际实现为 WttrInClient。
        $weather = $this->client->query($city);
        $location = $weather['location'];
        $current = $weather['current'];

        // 将供应商字段转换为应用自己的 DTO，避免上层依赖 wttr.in 的原始 JSON 结构。
        return new WeatherResult(
            city: $location['name'],
            country: $location['country'],
            latitude: $location['latitude'],
            longitude: $location['longitude'],
            temperature: $current['temperature'],
            apparentTemperature: $current['apparent_temperature'],
            humidity: $current['humidity'],
            precipitation: $current['precipitation'],
            windSpeed: $current['wind_speed'],
            weatherCode: $current['weather_code'],
            condition: $this->weatherCondition($current['weather_code']),
            observedAt: $current['time'],
            timezone: $current['timezone'],
            source: $weather['source'],
        );
    }

    private function weatherCondition(int $code): string
    {
        // wttr.in 使用 WorldWeatherOnline 天气码；这里统一转换成适合 Codex 回答的中文描述。
        return match ($code) {
            113 => '晴',
            116, 119 => '多云',
            122 => '阴',
            143, 248, 260 => '有雾',
            176, 263, 266, 281, 284, 293, 296, 299, 302, 305, 308, 311, 314, 317, 350, 353, 356, 359 => '有雨',
            179, 182, 185, 227, 230, 320, 323, 326, 329, 332, 335, 338, 362, 365, 368, 371 => '有雪',
            200, 386, 389, 392, 395 => '雷暴',
            default => '天气状况未知',
        };
    }
}
