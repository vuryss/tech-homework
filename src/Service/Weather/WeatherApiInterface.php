<?php

declare(strict_types=1);

namespace App\Service\Weather;

interface WeatherApiInterface
{
    /**
     * @param string $latitude
     * @param string $longitude
     *
     * @return Forecast[]
     */
    public function getForecastByCoordinates(string $latitude, string $longitude): iterable;
}
