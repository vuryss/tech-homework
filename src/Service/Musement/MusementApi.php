<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Exception\AppException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface as HttpClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MusementApi implements MusementApiInterface
{
    private HttpClientInterface $httpClient;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @return City[]
     * @throws AppException
     */
    public function getCities(): iterable
    {
        try {
            $response = $this->httpClient->request('GET', '/api/v3/cities');

            yield from $this->serializer->deserialize($response->getContent(), City::class . '[]', 'json');
        } catch (HttpClientExceptionInterface | SerializerExceptionInterface $exception) {
            $this->logger->error(
                'Error while retrieving cities from Musement API.',
                ['exception' => $exception]
            );
            throw new AppException('Error while retrieving weather data for a city.');
        }
    }
}
