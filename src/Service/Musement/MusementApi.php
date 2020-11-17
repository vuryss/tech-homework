<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Exception\AppException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use Symfony\Component\Serializer\SerializerInterface;

class MusementApi implements MusementApiInterface
{
    private Browser $httpClient;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        Browser $httpClient,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    public function getCities(): PromiseInterface
    {
        $url = 'https://api.musement.com/api/v3/cities';

        $this->logger->info('Sending GET request to: ' . $url);

        return $this
            ->httpClient
            ->get('https://api.musement.com/api/v3/cities')
            ->then(
                function (ResponseInterface $response) {
                    $this
                        ->logger
                        ->info($response->getStatusCode() . ' Response received.');

                    return $this->serializer->deserialize(
                        $response->getBody()->getContents(),
                        City::class . '[]',
                        'json'
                    );
                },
                function (Exception $exception) {
                    $this->logger->error(
                        'Error while retrieving cities from Musement API.',
                        ['exception' => $exception]
                    );
                    throw new AppException('Error while retrieving weather data for a city.');
                }
            );
    }
}
