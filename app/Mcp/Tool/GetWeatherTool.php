<?php

declare(strict_types=1);

namespace App\Mcp\Tool;

use App\Exception\Weather\CityNotFoundException;
use App\Exception\Weather\WeatherProviderException;
use App\Service\WeatherService;
use InvalidArgumentException;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\Content\TextContent;
use Mcp\Schema\Result\CallToolResult;
use Mcp\Schema\ToolAnnotations;

class GetWeatherTool
{
    public function __construct(private readonly WeatherService $weatherService)
    {
    }

    /**
     * MCP 工具入口。SDK 会根据方法参数生成 inputSchema：
     * {"type":"object","properties":{"city":{"type":"string"}},"required":["city"]}。
     *
     * @param string $city 要查询的城市名称，例如上海、北京或 Tokyo
     */
    #[McpTool(
        name: 'get_weather',
        title: '查询当前天气',
        description: '按城市名称查询当前天气。适用于用户询问某个城市现在的气温、湿度、风速或天气状况。',
        // 注解是提供给客户端和模型的行为提示：工具只读、无破坏性，但会访问外部天气服务。
        annotations: new ToolAnnotations(
            readOnlyHint: true,
            destructiveHint: false,
            openWorldHint: true,
        ),
        // structuredContent 必须符合这个输出 Schema，方便 Codex 稳定读取结构化字段。
        outputSchema: [
            'type' => 'object',
            'properties' => [
                'location' => ['type' => 'object'],
                'current' => ['type' => 'object'],
                'units' => ['type' => 'object'],
                'timezone' => ['type' => 'string'],
                'source' => ['type' => 'string'],
            ],
            'required' => ['location', 'current', 'units', 'timezone', 'source'],
        ],
    )]
    public function getWeather(string $city): CallToolResult
    {
        try {
            // REST 和 MCP 共用同一个业务服务，保证校验、天气码映射和返回字段一致。
            $result = $this->weatherService->query($city);

            return new CallToolResult(
                // content 给模型直接阅读；structuredContent 给客户端按字段消费。
                content: [new TextContent($result->summary())],
                structuredContent: $result->toArray(),
            );
        } catch (InvalidArgumentException | CityNotFoundException | WeatherProviderException $exception) {
            // 可预期的业务错误应作为 Tool Result 返回，并设置 isError=true。
            // 这样 Codex 能看到错误并调整城市参数，而不是收到 MCP 协议级异常。
            return new CallToolResult(
                content: [new TextContent($exception->getMessage())],
                isError: true,
            );
        }
    }
}
