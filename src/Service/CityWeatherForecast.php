<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Musement\City;
use App\Service\Musement\MusementApiInterface;
use App\Service\Weather\WeatherApiInterface;

class CityWeatherForecast
{
    private MusementApiInterface $musementApi;
    private WeatherApiInterface $weatherApi;

    public function __construct(MusementApiInterface $musementApi, WeatherApiInterface $weatherApi)
    {
        $this->musementApi = $musementApi;
        $this->weatherApi = $weatherApi;
    }

    /**
     * @return City[]
     */
    public function getCitiesWithForecast(): iterable
    {
        foreach ($this->musementApi->getCities() as $city) {
            $forecasts = $this->weatherApi->getForecastByCoordinates(
                $city->getLatitude(),
                $city->getLongitude()
            );

            foreach ($forecasts as $forecast) {
                $city->addForecast($forecast);
            }

            yield $city;
        }
    }
}
