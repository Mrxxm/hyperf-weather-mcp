<?php

declare(strict_types=1);

namespace App\Controller;

use App\Mcp\McpServerFactory;
use GuzzleHttp\Psr7\HttpFactory;
use Mcp\Server\Transport\StreamableHttpTransport;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class McpController
{
    public function __construct(private readonly McpServerFactory $serverFactory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // MCP SDK 基于 PSR-7/PSR-17 工作。Hyperf 已将当前 HTTP 请求注入为
        // ServerRequestInterface；Guzzle 的 HttpFactory 用来创建 SDK 所需的响应和流。
        $httpFactory = new HttpFactory();

        // StreamableHttpTransport 会识别请求属于哪一种 MCP 协议：
        // 1. 需要 initialize/session 的握手协议；
        // 2. 2026-07-28 这类每次请求都携带 _meta 的无状态协议。
        // 它也负责 JSON-RPC 编解码、协议请求头检查以及 MCP 会话头处理。
        $transport = new StreamableHttpTransport(
            request: $request,
            responseFactory: $httpFactory,
            streamFactory: $httpFactory,
        );

        // MCP Server 将 JSON-RPC 方法分派给已发现的 Tool。
        // SDK 返回标准 PSR-7 Response，直接交给 Hyperf 发送，避免二次 JSON 编码。
        return $this->serverFactory->create()->run($transport);
    }
}
