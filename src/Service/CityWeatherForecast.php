<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Musement\City;
use App\Service\Musement\MusementApiInterface;
use App\Service\Forecast\ForecastApiInterface;

class CityWeatherForecast
{
    private MusementApiInterface $musementApi;
    private ForecastApiInterface $weatherApi;

    public function __construct(MusementApiInterface $musementApi, ForecastApiInterface $weatherApi)
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
                $city->addForecastForDate($forecast, $forecast->getDate());
            }

            yield $city;
        }
    }
}
