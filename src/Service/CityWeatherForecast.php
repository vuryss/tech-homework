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
     * @param int $days
     *
     * @return City[]
     */
    public function getCitiesWithForecastForDays(int $days): iterable
    {
        foreach ($this->musementApi->getCities() as $city) {
            $forecasts = $this->weatherApi->getCityForecasts($city, $days);

            foreach ($forecasts as $forecast) {
                $city->addForecast($forecast);
            }

            yield $city;
        }
    }
}
