<?php

declare(strict_types=1);

namespace App\Service\Weather;

use App\Exception\AppException;
use App\Service\ExceptionFormatter;
use DateTimeImmutable;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherApi implements WeatherApiInterface
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $weatherApiHttpClient, LoggerInterface $logger, string $apiKey)
    {
        $this->httpClient = $weatherApiHttpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
    }

    /**
     * @throws AppException
     *
     * @param string $longitude
     * @param string $latitude
     *
     * @return iterable
     */
    public function getForecastByCoordinates(string $latitude, string $longitude): iterable
    {
        $queryParameters = [
            'key' => $this->apiKey,
            'days' => 2,
            'q' => $latitude . ',' . $longitude,
        ];

        try {
            $response = $this->httpClient->request(
                'GET',
                '/v1/forecast.json',
                [
                    'query' => $queryParameters
                ]
            );

            $forecast = $response->toArray();
        } catch (HttpExceptionInterface|ExceptionInterface $exception) {
            $this->logger->error(ExceptionFormatter::formatForLog($exception));
            throw new AppException('Error while retrieving weather data for a city.');
        }

        yield from $this->parseForecastsResponse($forecast);
    }

    /**
     * @throws AppException
     *
     * @param array $forecast
     *
     * @return iterable
     */
    private function parseForecastsResponse(array $forecast): iterable
    {
        if (!isset($forecast['forecast']['forecastday']) || !is_array($forecast['forecast']['forecastday'])) {
            $this->logger->error(
                'Invalid JSON structure returned from Weather API. Cannot parse forecast.',
                $forecast
            );

            throw new AppException('Error while retrieving weather data for a city.');
        }

        foreach ($forecast['forecast']['forecastday'] as $dayForecast) {
            if (
                !isset($dayForecast['date_epoch'])
                || !ctype_digit($dayForecast['date_epoch'])
                || !isset($dayForecast['day']['condition']['text'])
                || !is_string($dayForecast['day']['condition']['text'])
            ) {
                $this->logger->error(
                    'Invalid JSON data returned from Weather API. Cannot parse forecast.',
                    $dayForecast
                );

                throw new AppException('Error while retrieving weather data for a city.');
            }

            try {
                yield (new Forecast())
                    ->setDate(new DateTimeImmutable('@' . $dayForecast['date_epoch']))
                    ->setWeather($dayForecast['day']['condition']['text']);
            } catch (Exception $exception) {
                $this->logger->error(
                    'Cannot parse timestamp: ' . $dayForecast['date_epoch'],
                    $dayForecast
                );

                throw new AppException('Error while retrieving weather data for a city.');
            }
        }
    }
}
