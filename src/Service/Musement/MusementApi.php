<?php

declare(strict_types=1);

namespace App\Service\Musement;

use App\Exception\AppException;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MusementApi implements MusementApiInterface
{
    private Browser $httpClient;
    private string $url;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private ValidatorInterface $validator;

    public function __construct(
        Browser $httpClient,
        string $url,
        SerializerInterface $serializer,
        LoggerInterface $logger,
        ValidatorInterface $validator
    ) {
        $this->httpClient = $httpClient;
        $this->url = $url;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->validator = $validator;
    }

    public function getCities(): PromiseInterface
    {
        $this->logger->info('Sending GET request to: ' . $this->url);

        return $this
            ->httpClient
            ->get($this->url)
            ->then(
                fn (ResponseInterface $response) => $this->handleHttpSuccess($response),
                fn (Exception $exception) => $this->handleHttpError($exception)
            );
    }

    /**
     * @param ResponseInterface $response
     *
     * @return City[]
     * @throws AppException
     */
    private function handleHttpSuccess(ResponseInterface $response): array
    {
        $this
            ->logger
            ->info($response->getStatusCode() . ' Response received.');

        $cities = $this->serializer->deserialize(
            $response->getBody()->getContents(),
            City::class . '[]',
            'json',
            [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
        );

        $errors = $this->validator->validate($cities);

        if (count($errors) > 0) {
            $this
                ->logger
                ->error(
                    'One or more cities could not be parsed from Musement API response. Errors: ' . (string)$errors
                );

            throw new AppException('One or more cities could not be parsed from Musement API response.');
        }

        return $cities;
    }

    /**
     * @param Exception $exception
     *
     * @throws AppException
     */
    private function handleHttpError(Exception $exception): void
    {
        $this->logger->error(
            'Error while retrieving cities from Musement API.',
            ['exception' => $exception]
        );

        throw new AppException('Error while retrieving cities from Musement API.', 0, $exception);
    }
}
