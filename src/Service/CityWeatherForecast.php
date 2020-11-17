<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Musement\MusementApiInterface;
use App\Service\Forecast\ForecastApiInterface;
use React\Promise\PromiseInterface;

use function React\Promise\all;

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
     * @return PromiseInterface
     */
    public function getCitiesWithForecastForDays(int $days): PromiseInterface
    {
        return $this
            ->musementApi
            ->getCities()
            ->then(
                function (iterable $cities) use ($days) {
                    $forecastPromises = [];

                    foreach ($cities as $city) {
                        $forecastPromises[] = $this
                            ->weatherApi
                            ->getCityForecasts($city, $days)
                            ->then(
                                function (iterable $forecasts) use ($city) {
                                    foreach ($forecasts as $forecast) {
                                        $city->addForecast($forecast);
                                    }

                                    return $city;
                                }
                            );
                    }

                    return all($forecastPromises);
                }
            );
    }
}
