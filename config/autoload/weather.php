<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'base_url' => env('WEATHER_BASE_URL', 'https://wttr.in'),
    'connect_timeout' => (float) env('WEATHER_CONNECT_TIMEOUT', 10),
    'timeout' => (float) env('WEATHER_TIMEOUT', 30),
];
