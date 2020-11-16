<?php

declare(strict_types=1);

namespace App\Service\Musement;

interface MusementApiInterface
{
    /**
     * @return City[]
     */
    public function getCities(): iterable;
}
