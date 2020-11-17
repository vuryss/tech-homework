<?php

declare(strict_types=1);

namespace App\Service\Musement;

use React\Promise\PromiseInterface;

interface MusementApiInterface
{
    public function getCities(): PromiseInterface;
}
