<?php

declare(strict_types=1);

namespace App\Service\Forecast\WeatherApi;

use App\Exception\AppException;
use App\Service\Forecast\Forecast;
use App\Service\Forecast\ForecastApiInterface;
use App\Service\Musement\City;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherApi implements ForecastApiInterface
{
    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
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
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @throws AppException
     *
     * @param City $city
     * @param int  $days
     *
     * @return Forecast[]
     */
    public function getCityForecasts(City $city, int $days = 1): iterable
    {
        $queryParameters = [
            'key' => $this->apiKey,
            'days' => $days,
            'q' => $city->getLatitude() . ',' . $city->getLongitude(),
        ];

        try {
            $response = $this->httpClient->request(
                'GET',
                '/v1/forecast.json',
                [
                    'query' => $queryParameters
                ]
            );

            yield from $this->serializer->deserialize($response->getContent(), Forecast::class . '[]', 'json');
        } catch (HttpClientExceptionInterface | SerializerExceptionInterface $exception) {
            $this->logger->error(
                'Error while retrieving weather data for a city.',
                ['exception' => $exception]
            );
            throw new AppException('Error while retrieving weather data for a city.');
        }
    }
}
