<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Service\Weather\Forecast;
use DateTimeImmutable;

class City
{
    private string $name;
    private string $latitude;
    private string $longitude;

    /**
     * @var Forecast[]
     */
    private array $dailyForecast;

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude ?? null;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude ?? null;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function addForecast(Forecast $forecast)
    {
        $this->dailyForecast[$forecast->getDate()->format('Y-m-d')] = $forecast;
    }

    public function getForecastForDay(DateTimeImmutable $date): ?Forecast
    {
        return $this->dailyForecast[$date->format('Y-m-d')] ?? null;
    }
}
