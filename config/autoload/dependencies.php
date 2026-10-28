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
use App\Client\WeatherClientInterface;
use App\Client\WttrInClient;

return [
    // 业务层只依赖接口。以后更换天气供应商时，只需替换这里的实现类，
    // REST Controller 和 MCP Tool 都不需要修改。
    WeatherClientInterface::class => WttrInClient::class,
];
