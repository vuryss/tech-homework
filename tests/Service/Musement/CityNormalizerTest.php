<?php

declare(strict_types=1);

namespace App\Tests\Service\Musement;

use App\Service\Forecast\Forecast;
use App\Service\Musement\City;
use App\Service\Musement\CityNormalizer;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class CityNormalizerTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testValidData()
    {
        $city = (new City())
            ->setName('Test city')
            ->setLatitude('1.2345')
            ->setLongitude('6.789');

        $apiResponseDecoded = [
            'name' => $city->getName(),
            'latitude' => (float)$city->getLatitude(),
            'longitude' => (float)$city->getLongitude(),
        ];

        $mockLogger = $this->createMock(LoggerInterface::class);

        $normalizer = new CityNormalizer($mockLogger);
        $result = $normalizer->denormalize($apiResponseDecoded, City::class, 'json');

        $this->assertEquals($city->getName(), $result->getName());
        $this->assertEquals($city->getLatitude(), $result->getLatitude());
        $this->assertEquals($city->getLongitude(), $result->getLongitude());
    }

    public function testSupports()
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $normalizer = new CityNormalizer($mockLogger);
        $this->assertTrue($normalizer->supportsDenormalization([], City::class));
        $this->assertFalse($normalizer->supportsDenormalization([], Forecast::class));
    }

    /**
     * @param $data
     *
     * @dataProvider invalidDataProvider
     */
    public function testInvalidDate($data)
    {
        $this->expectException(ExceptionInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $normalizer = new CityNormalizer($mockLogger);
        $result = $normalizer->denormalize($data, Forecast::class, 'json');

        if ($result instanceof Generator) {
            $result->current();
        }
    }

    public function invalidDataProvider()
    {
        return [
            'Missing Name' => [
                [
                    'latitude' => 1.1234,
                    'longitude' => 5.6789,
                ]
            ],
            'Missing latitude' => [
                [
                    'name' => 'Test city',
                    'longitude' => 5.6789,
                ]
            ],
            'Missing longitude' => [
                [
                    'name' => 'Test city',
                    'latitude' => 1.1234,
                ]
            ],
            'Wrong name format' => [
                [
                    'name' => ['Test city'],
                    'latitude' => 1.1234,
                    'longitude' => 5.6789,
                ]
            ],
            'Wrong lat format' => [
                [
                    'name' => 'Test city',
                    'latitude' => [1.1234],
                    'longitude' => 5.6789,
                ]
            ],
            'Wrong long format' => [
                [
                    'name' => 'Test city',
                    'latitude' => 1.1234,
                    'longitude' => 'asd',
                ]
            ],
        ];
    }
}
