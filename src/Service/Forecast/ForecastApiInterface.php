<?php

declare(strict_types=1);

namespace App\Service\Forecast;

use App\Service\Musement\City;
use React\Promise\PromiseInterface;

interface ForecastApiInterface
{
    /**
     * @param City $city
     * @param int  $days
     *
     * @return PromiseInterface
     */
    public function getCityForecasts(City $city, int $days = 1): PromiseInterface;
}
