<?php

declare(strict_types=1);

namespace App\Service;

use Throwable;

class ExceptionFormatter
{
    public static function formatForLog(Throwable $throwable): string
    {
        $logString = get_class($throwable)
            . ' -> '
            . $throwable->getMessage()
            . PHP_EOL
            . $throwable->getTraceAsString();

        if ($throwable->getPrevious() !== null) {
            $logString .= PHP_EOL
                . PHP_EOL
                . self::formatForLog($throwable->getPrevious());
        }

        return $logString;
    }
}
