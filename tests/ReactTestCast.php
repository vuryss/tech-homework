<?php

declare(strict_types=1);

namespace App\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use React\Promise\PromiseInterface;

class ReactTestCast extends TestCase
{
    protected function assertPromiseFailsWithException(PromiseInterface $promise, string $exceptionClass)
    {
        $thrownException = null;

        $promise
            ->then(
                null,
                function (Exception $resultException) use (&$thrownException) {
                    $thrownException = $resultException;
                }
            );

        $this->assertInstanceOf($exceptionClass, $thrownException);
    }
}
