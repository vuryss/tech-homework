<?php

declare(strict_types=1);

namespace App\Service\Forecast\WeatherApi;

use App\Exception\AppException;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\ForecastApiInterface;
use App\Service\Musement\City;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class WeatherApi implements ForecastApiInterface
{
    private Browser $httpClient;
    private string $url;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private string $apiKey;

    public function __construct(
        Browser $httpClient,
        string $url,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $apiKey
    ) {
        $this->httpClient = $httpClient;
        $this->url = $url;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
    }

    /**
     * @param City $city
     * @param int  $days
     *
     * @return PromiseInterface
     */
    public function getCityForecasts(City $city, int $days = 1): PromiseInterface
    {
        $queryParameters = [
            'key' => $this->apiKey,
            'days' => $days,
            'q' => $city->getLatitude() . ',' . $city->getLongitude(),
        ];

        $this->logger->info('Sending GET request to: ' . $this->url);

        return $this
            ->httpClient
            ->get($this->url . '?' . http_build_query($queryParameters))
            ->then(
                fn (ResponseInterface $response) => $this->handleHttpSuccess($response),
                fn (Exception $exception) => $this->handleHttpError($exception)
            );
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Forecast[]
     */
    private function handleHttpSuccess(ResponseInterface $response): iterable
    {
        $this
            ->logger
            ->info($response->getStatusCode() . ' Response received.');

        $forecasts = $this
            ->serializer
            ->deserialize(
                $response->getBody()->getContents(),
                Forecast::class . '[]',
                'json',
                [UnwrappingDenormalizer::UNWRAP_PATH => '[forecast][forecastday]']
            );

        return $forecasts;
    }

    /**
     * @param Exception $exception
     *
     * @throws AppException
     */
    private function handleHttpError(Exception $exception): void
    {
        $this->logger->error(
            'Error while retrieving weather data for a city.',
            ['exception' => $exception]
        );

        throw new AppException('Error while retrieving weather data for a city.', 0, $exception);
    }
}
