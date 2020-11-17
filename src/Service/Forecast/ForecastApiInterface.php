<?php

declare(strict_types=1);

namespace App\Service\Forecast;

use App\Service\Musement\City;

interface ForecastApiInterface
{
    /**
     * @param City $city
     * @param int  $days
     *
     * @return Forecast[]
     */
    public function getCityForecasts(City $city, int $days = 1): iterable;
}
