<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\Weather\CityNotFoundException;
use App\Exception\Weather\WeatherProviderException;
use App\Service\WeatherService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class WeatherController
{
    public function __construct(private readonly WeatherService $weatherService)
    {
    }

    public function current(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $result = $this->weatherService->query((string) $request->input('city', ''));

            return $response->json([
                'code' => 0,
                'message' => 'ok',
                'data' => $result->toArray(),
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->error($response, 400, $exception->getMessage());
        } catch (CityNotFoundException $exception) {
            return $this->error($response, 404, $exception->getMessage());
        } catch (WeatherProviderException $exception) {
            return $this->error($response, 502, $exception->getMessage());
        }
    }

    private function error(ResponseInterface $response, int $status, string $message): PsrResponseInterface
    {
        return $response->json([
            'code' => $status,
            'message' => $message,
            'data' => null,
        ])->withStatus($status);
    }
}
