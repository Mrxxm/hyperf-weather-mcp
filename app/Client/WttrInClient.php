<?php

declare(strict_types=1);

namespace App\Client;

use App\Exception\Weather\CityNotFoundException;
use App\Exception\Weather\WeatherProviderException;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use JsonException;

class WttrInClient implements WeatherClientInterface
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly ConfigInterface $config,
    ) {
    }

    public function query(string $city): array
    {
        $baseUrl = rtrim((string) $this->config->get('weather.base_url'), '/');

        // format=j1 返回 JSON。城市放入 URL 前必须编码，确保中文、空格等字符可安全传输。
        $payload = $this->getJson($baseUrl . '/' . rawurlencode($city), ['format' => 'j1']);

        // 首版只取供应商返回的第一个地点和当前天气，不处理同名城市候选列表。
        $area = $payload['nearest_area'][0] ?? null;
        $current = $payload['current_condition'][0] ?? null;
        if (! is_array($area) || ! is_array($current)) {
            throw new CityNotFoundException("未找到城市：{$city}");
        }

        $region = $this->nestedValue($area, 'region');
        $areaName = $this->nestedValue($area, 'areaName');
        $resolvedName = $region !== '' ? $region : $areaName;
        if ($resolvedName === '') {
            throw new CityNotFoundException("未找到城市：{$city}");
        }

        // 在 Client 边界完成第三方字段归一化；WeatherService 不读取供应商原始字段名。
        return [
            'location' => [
                'name' => $resolvedName,
                'country' => $this->nestedValue($area, 'country'),
                'latitude' => (float) ($area['latitude'] ?? 0),
                'longitude' => (float) ($area['longitude'] ?? 0),
            ],
            'current' => [
                'temperature' => (float) ($current['temp_C'] ?? 0),
                'apparent_temperature' => (float) ($current['FeelsLikeC'] ?? 0),
                'humidity' => (int) ($current['humidity'] ?? 0),
                'precipitation' => (float) ($current['precipMM'] ?? 0),
                'weather_code' => (int) ($current['weatherCode'] ?? -1),
                'wind_speed' => (float) ($current['windspeedKmph'] ?? 0),
                'time' => (string) ($current['localObsDateTime'] ?? $current['observation_time'] ?? ''),
                'timezone' => '',
            ],
            'source' => 'wttr.in',
        ];
    }

    /** @return array<string, mixed> */
    private function getJson(string $url, array $query): array
    {
        try {
            // Hyperf 的 ClientFactory 创建协程友好的 Guzzle Client。
            // 超时来自 config/autoload/weather.php，防止 MCP 工具调用无限等待上游。
            $client = $this->clientFactory->create([
                'connect_timeout' => (float) $this->config->get('weather.connect_timeout', 10),
                'timeout' => (float) $this->config->get('weather.timeout', 30),
                'headers' => ['User-Agent' => 'hyperf-weather-mcp/1.0'],
                'http_errors' => true,
            ]);
            $response = $client->request('GET', $url, ['query' => $query]);
            $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException | JsonException $exception) {
            // 屏蔽 Guzzle/JSON 的底层异常类型，上层只处理稳定的领域异常。
            throw new WeatherProviderException('调用 wttr.in 失败：' . $exception->getMessage(), 0, $exception);
        }

        if (! is_array($data)) {
            throw new WeatherProviderException('天气服务返回了无效数据');
        }

        return $data;
    }

    /** @param array<string, mixed> $area */
    private function nestedValue(array $area, string $field): string
    {
        return (string) ($area[$field][0]['value'] ?? '');
    }
}
