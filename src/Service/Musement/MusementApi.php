<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Exception\AppException;
use App\Service\ExceptionFormatter;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusementApi implements MusementApiInterface
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $musementApiHttpClient, LoggerInterface $logger)
    {
        $this->httpClient = $musementApiHttpClient;
        $this->logger = $logger;
    }

    /**
     * @throws AppException
     *
     * @return City[]
     */
    public function getCities(): iterable
    {
        try {
            $cities = $this->httpClient->request('GET', '/api/v3/cities')->toArray();
        } catch (HttpExceptionInterface|ExceptionInterface $exception) {
            $this->logger->error(ExceptionFormatter::formatForLog($exception));
            throw new AppException('Error while retrieving weather data for a city.');
        }

        yield from $this->parseCitiesResponse($cities);
    }

    /**
     * @throws AppException
     *
     * @param array $cities
     *
     * @return City[]
     */
    private function parseCitiesResponse(array $cities): iterable
    {
        foreach ($cities as $cityData) {
            if (
                !isset($cityData['name'])
                || !isset($cityData['latitude'])
                || !is_numeric($cityData['latitude'])
                || !isset($cityData['longitude'])
                || !is_numeric($cityData['longitude'])
            ) {
                $this->logger->error(
                    'Invalid JSON data returned from Musement API. Cannot parse city data.',
                    $cityData
                );

                throw new AppException('Error while retrieving city data.');
            }

            yield (new City())
                ->setName($cityData['name'])
                ->setLatitude((string) $cityData['latitude'])
                ->setLongitude((string) $cityData['longitude']);
        }
    }
}
