<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Service\Forecast\Forecast;
use Symfony\Component\Validator\Constraints as Assert;

class City
{
    /**
     * @Assert\NotBlank
     */
    private string $name;

    /**
     * @Assert\NotBlank
     * @Assert\Regex("/^-?\d+(\.\d+)?$/")
     */
    private string $latitude;

    /**
     * @Assert\NotBlank
     * @Assert\Regex("/^-?\d+(\.\d+)?$/")
     */
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

    public function addForecast(Forecast $forecast): self
    {
        $this->dailyForecast[$forecast->getDate()->format('Y-m-d')] = $forecast;

        return $this;
    }

    /**
     * @return Forecast[]]|null
     */
    public function getForecasts(): array
    {
        return $this->dailyForecast ?? [];
    }
}
