<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use App\Controller\IndexController;
use App\Controller\McpController;
use App\Controller\WeatherController;
use Hyperf\HttpServer\Router\Router;

Router::get('/', [IndexController::class, 'index']);
Router::get('/weather', [WeatherController::class, 'current']);

// MCP 的 Streamable HTTP 端点：
// POST 承载 JSON-RPC 请求；GET/DELETE/OPTIONS 用于传输层生命周期、会话关闭和跨域预检。
// 这些请求全部交给 MCP SDK 处理，不能套用普通 REST 的 code/message/data 响应格式。
Router::addRoute(['GET', 'POST', 'DELETE', 'OPTIONS'], '/mcp', [McpController::class, 'handle']);

Router::get('/favicon.ico', function () {
    return '';
});
