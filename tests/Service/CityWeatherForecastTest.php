<?php

/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpParamsInspection */

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\CityWeatherForecast;
use App\Service\Musement\City;
use App\Service\Musement\MusementApiInterface;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\ForecastApiInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CityWeatherForecastTest extends TestCase
{
    public function testGetCitiesWithForecast()
    {
        $mockWeatherApi = $this->createMock(ForecastApiInterface::class);
        $mockMusementApi = $this->createMock(MusementApiInterface::class);

        $city = (new City())
            ->setName('Test city')
            ->setLatitude('1.234')
            ->setLongitude('5.678');

        $forecast1 = (new Forecast())
            ->setDate(new DateTimeImmutable('2020-10-20'))
            ->setWeather('Weather today');

        $forecast2 = (new Forecast())
            ->setDate(new DateTimeImmutable('2020-10-21'))
            ->setWeather('Weather tomorrow');

        $mockMusementApi
            ->expects($this->once())
            ->method('getCities')
            ->willReturn([$city]);

        $mockWeatherApi
            ->expects($this->once())
            ->method('getForecastByCoordinates')
            ->with(
                $this->callback(
                    function (string $latitude) use ($city) {
                        return $city->getLatitude() === $latitude;
                    }
                ),
                $this->callback(
                    function (string $longitude) use ($city) {
                        return $city->getLongitude() === $longitude;
                    }
                )
            )
            ->willReturn([$forecast1, $forecast2]);

        $cityWeatherForecast = new CityWeatherForecast($mockMusementApi, $mockWeatherApi);

        $cities = $cityWeatherForecast->getCitiesWithForecast();

        foreach ($cities as $outputCity) {
            $this->assertEquals($city, $outputCity);

            $this->assertEquals($forecast1, $city->getForecastForDay(new DateTimeImmutable('2020-10-20')));
            $this->assertEquals($forecast2, $city->getForecastForDay(new DateTimeImmutable('2020-10-21')));
        }
    }
}
