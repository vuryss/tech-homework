<?php

declare(strict_types=1);

namespace App\Service\Weather;

use DateTimeImmutable;

class Forecast
{
    private DateTimeImmutable $date;
    private string $weather;

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date ?? null;
    }

    public function setDate(DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getWeather(): ?string
    {
        return $this->weather ?? null;
    }

    public function setWeather(string $weather): self
    {
        $this->weather = $weather;

        return $this;
    }
}
