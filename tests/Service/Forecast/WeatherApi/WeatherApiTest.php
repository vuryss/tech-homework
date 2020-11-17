<?php

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Forecast\WeatherApi;

use App\Exception\AppException;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\WeatherApi\WeatherApi;
use App\Service\Musement\City;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
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

        $city = (new City())
            ->setLatitude('1.234')
            ->setLongitude('5.678');

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
        $forecasts = $musementApi->getCityForecasts($city, 1);

        foreach ($forecasts as $forecastResult) {
            $this->assertEquals(
                $forecast->getDate()->format('Y-m-d'),
                $forecastResult->getDate()->format('Y-m-d')
            );
            $this->assertEquals($forecast->getWeather(), $forecastResult->getWeather());
        }
    }

    public function testHttpException()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockException = $this->createMock(ExceptionInterface::class);

        $this->expectException(AppException::class);

        $city = (new City())
            ->setLatitude('1.234')
            ->setLongitude('5.678');

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $mockResponse
            ->expects($this->once())
            ->method('getContent')
            ->willThrowException($mockException);

        $musementApi = new WeatherApi($mockHttpClient, $mockSerializer, $mockLogger, 'api-key');
        $result = $musementApi->getCityForecasts($city, 1);

        if ($result instanceof Generator) {
            $result->current();
        }
    }
}
