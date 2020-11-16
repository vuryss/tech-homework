<?php

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Forecast\WeatherApi;

use App\Exception\AppException;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\WeatherApi\WeatherApi;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherApiTest extends TestCase
{
    /**
     * @throws AppException
     */
    public function testValidResponse()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $date = new DateTimeImmutable('now');

        $forecast = (new Forecast())
            ->setWeather('Some weather')
            ->setDate($date);

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $mockSerializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([$forecast]);

        $musementApi = new WeatherApi($mockHttpClient, $mockSerializer, $mockLogger, 'api-key');
        $forecasts = $musementApi->getForecastByCoordinates('1.234', '5.678');

        foreach ($forecasts as $forecastResult) {
            $this->assertEquals(
                $forecast->getDate()->format('Y-m-d'),
                $forecastResult->getDate()->format('Y-m-d')
            );
            $this->assertEquals($forecast->getWeather(), $forecastResult->getWeather());
        }
    }
}
