<?php

declare(strict_types=1);

namespace App\Service\Forecast\WeatherApi;

use App\Service\Forecast\Forecast;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class WeatherApiForecastNormalizer implements DenormalizerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $weatherApiLogger)
    {
        $this->logger = $weatherApiLogger;
    }

    /**
     * @param array       $data
     * @param string      $type
     * @param string|null $format
     * @param array       $context
     *
     * @return Forecast
     * @throws Exception
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (
            !isset($data['hour'][0]['time_epoch'])
            || !ctype_digit($data['hour'][0]['time_epoch'])
            || !isset($data['day']['condition']['text'])
            || !is_string($data['day']['condition']['text'])
        ) {
            $this->logger->error(
                'Invalid JSON data returned from Weather API. Cannot parse forecast.',
                $data
            );

            throw new UnexpectedValueException('Error while retrieving weather data for a city.');
        }

        return (new Forecast())
            ->setDate((new DateTimeImmutable('@' . $data['hour'][0]['time_epoch'])))
            ->setWeather($data['day']['condition']['text']);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === Forecast::class;
    }
}
