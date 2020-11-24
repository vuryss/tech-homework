<?php

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Forecast\WeatherApi;

use App\Exception\AppException;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\WeatherApi\WeatherApi;
use App\Service\Musement\City;
use App\Tests\ReactTestCast;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;
use Symfony\Component\Serializer\SerializerInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

class WeatherApiTest extends ReactTestCast
{
    public function testValidResponse()
    {
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $browser = $this->createMock(Browser::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $date = new DateTimeImmutable('now');

        $city = (new City())
            ->setLatitude('1.234')
            ->setLongitude('5.678');

        $forecast = (new Forecast())
            ->setWeather('Some weather')
            ->setDate($date);

        $browser
            ->expects($this->once())
            ->method('get')
            ->willReturn(resolve($response));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $mockSerializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([$forecast]);

        $musementApi = new WeatherApi($browser, '', $mockSerializer, $mockLogger, 'api-key');
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
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $browser = $this->createMock(Browser::class);
        $exception = new Exception('Some error');

        $browser
            ->expects($this->once())
            ->method('get')
            ->willReturn(reject($exception));

        $city = (new City())
            ->setLatitude('1.234')
            ->setLongitude('5.678');

        $musementApi = new WeatherApi($browser, '', $mockSerializer, $mockLogger, 'api-key');
        $result = $musementApi->getCityForecasts($city, 1);

        $this->assertPromiseFailsWithException($result, AppException::class);
    }
}
