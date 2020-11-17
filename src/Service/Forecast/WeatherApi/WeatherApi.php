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
use Symfony\Component\Serializer\SerializerInterface;

class WeatherApi implements ForecastApiInterface
{
    private Browser $httpClient;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private string $apiKey;

    public function __construct(
        Browser $httpClient,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        string $apiKey
    ) {
        $this->httpClient = $httpClient;
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

        return $this
            ->httpClient
            ->get('https://api.weatherapi.com/v1/forecast.json?' . http_build_query($queryParameters))
            ->then(
                function (ResponseInterface $response) {
                    return $this
                        ->serializer
                        ->deserialize(
                            $response->getBody()->getContents(),
                            Forecast::class . '[]',
                            'json'
                        );
                },
                function (Exception $exception) {
                    $this->logger->error(
                        'Error while retrieving weather data for a city.',
                        ['exception' => $exception]
                    );
                    throw new AppException('Error while retrieving weather data for a city.');
                }
            );
    }
}
