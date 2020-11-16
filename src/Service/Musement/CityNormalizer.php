<?php

declare(strict_types=1);

namespace App\Service\Musement;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CityNormalizer implements DenormalizerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $musementApiLogger)
    {
        $this->logger = $musementApiLogger;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $this->validateData($data);

        return (new City())
            ->setName($data['name'])
            ->setLatitude((string) $data['latitude'])
            ->setLongitude((string) $data['longitude']);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === City::class;
    }

    /**
     * @param $data
     *
     * @throws UnexpectedValueException
     */
    private function validateData($data)
    {
        if (
            !isset($data['name'])
            || !is_string($data['name'])
            || !isset($data['latitude'])
            || !is_numeric($data['latitude'])
            || !isset($data['longitude'])
            || !is_numeric($data['longitude'])
        ) {
            $this->logger->error(
                'Invalid JSON data returned from Musement API. Cannot parse city data.',
                $data
            );

            throw new UnexpectedValueException('Error while retrieving city data.');
        }
    }
}
