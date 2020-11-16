<?php

declare(strict_types=1);

namespace App\Service\Forecast\WeatherApi;

use App\Service\Forecast\Forecast;
use DateTimeImmutable;
use Exception;
use Generator;
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
     * @throws Exception
     *
     * @param string      $type
     * @param string|null $format
     * @param array       $context
     * @param mixed       $data
     *
     * @return Forecast[]|Generator
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (!isset($data['forecast']['forecastday']) || !is_array($data['forecast']['forecastday'])) {
            $this->logger->error(
                'Invalid JSON structure returned from Weather API. Cannot parse forecast.',
                $data
            );

            throw new UnexpectedValueException('Error while retrieving weather data for a city.');
        }

        foreach ($data['forecast']['forecastday'] as $dayForecast) {
            yield from $this->parseForecastDay($dayForecast);
        }
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === Forecast::class . '[]';
    }

    /**
     * @throws UnexpectedValueException|Exception
     *
     * @param array $forecastDay
     *
     * @return Forecast[]
     */
    private function parseForecastDay(array $forecastDay): iterable
    {
        if (
            !isset($forecastDay['hour'][0]['time_epoch'])
            || !ctype_digit($forecastDay['hour'][0]['time_epoch'])
            || !isset($forecastDay['day']['condition']['text'])
            || !is_string($forecastDay['day']['condition']['text'])
        ) {
            $this->logger->error(
                'Invalid JSON data returned from Weather API. Cannot parse forecast.',
                $forecastDay
            );

            throw new UnexpectedValueException('Error while retrieving weather data for a city.');
        }

        yield (new Forecast())
            ->setDate((new DateTimeImmutable('@' . $forecastDay['hour'][0]['time_epoch'])))
            ->setWeather($forecastDay['day']['condition']['text']);
    }
}
