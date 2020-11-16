<?php

/** @noinspection PhpStatementHasEmptyBodyInspection */

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Musement;

use App\Exception\AppException;
use App\Service\Musement\City;
use App\Service\Musement\MusementApi;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MusementApiTest extends TestCase
{
    /**
     * @throws AppException
     */
    public function testValidResponse()
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $city = (new City())
            ->setName('Test city')
            ->setLatitude('1.2345')
            ->setLongitude('6.789');

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $mockSerializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([$city]);

        $musementApi = new MusementApi($mockHttpClient, $mockSerializer, $mockLogger);
        $cities = $musementApi->getCities();

        foreach ($cities as $cityResult) {
            $this->assertEquals($city->getName(), $cityResult->getName());
            $this->assertEquals($city->getLatitude(), $cityResult->getLatitude());
            $this->assertEquals($city->getLongitude(), $cityResult->getLongitude());
        }
    }

    public function testHttpException()
    {
        $this->expectException(AppException::class);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($this->createMock(HttpExceptionInterface::class));

        $musementApi = new MusementApi($mockHttpClient, $mockSerializer, $mockLogger);
        $cities = $musementApi->getCities();

        if ($cities instanceof Generator) {
            $cities->current();
        }
    }
}
