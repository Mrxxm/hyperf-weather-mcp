<?php

declare(strict_types=1);

namespace App\Mcp;

use Mcp\Server;
use Psr\Container\ContainerInterface;

class McpServerFactory
{
    // Server 必须在当前 Worker 内复用。握手式 MCP 的 SessionManager 保存在 Server 中；
    // 如果每个 HTTP 请求都重新 build，initialize 之后的 tools/list 会找不到 Session。
    private ?Server $server = null;

    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function create(): Server
    {
        return $this->server ??= Server::builder()
            // 这些信息会出现在 initialize 响应以及 2026-07-28 响应的 _meta 中。
            ->setServerInfo(
                name: 'hyperf-weather-mcp',
                version: '1.0.0',
                description: '本机 Hyperf 天气查询 MCP 服务',
                title: 'Hyperf Weather MCP',
            )
            // 提供给 MCP 客户端/模型的服务级使用说明，不会直接触发工具调用。
            ->setInstructions('使用 get_weather 工具按城市名称查询当前天气。')
            // 将 Hyperf 的 PSR-11 容器交给 SDK。SDK 实例化 GetWeatherTool 时，
            // WeatherService、WeatherClientInterface 等依赖继续由 Hyperf 自动注入。
            ->setContainer($this->container)
            // 扫描目录中的 #[McpTool] 属性并生成工具注册表和 JSON Schema。
            ->setDiscovery(
                basePath: BASE_PATH,
                scanDirs: ['app/Mcp/Tool'],
                excludeDirs: ['vendor', 'runtime'],
            )
            ->build();
    }
}
