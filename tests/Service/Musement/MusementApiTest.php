<?php

/** @noinspection PhpStatementHasEmptyBodyInspection */

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Musement;

use App\Exception\AppException;
use App\Service\Musement\City;
use App\Service\Musement\MusementApi;
use App\Tests\ReactTestCast;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;
use Symfony\Component\Serializer\SerializerInterface;

use function React\Promise\reject;
use function React\Promise\resolve;

class MusementApiTest extends ReactTestCast
{
    public function testValidResponse()
    {
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $browser = $this->createMock(Browser::class);
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $city = (new City())
            ->setName('Test city')
            ->setLatitude('1.2345')
            ->setLongitude('6.789');

        $browser
            ->expects($this->once())
            ->method('get')
            ->willReturn(resolve($response));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $mockSerializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([$city]);

        $musementApi = new MusementApi($browser, $mockSerializer, $mockLogger);
        $cities = $musementApi->getCities();

        foreach ($cities as $cityResult) {
            $this->assertEquals($city->getName(), $cityResult->getName());
            $this->assertEquals($city->getLatitude(), $cityResult->getLatitude());
            $this->assertEquals($city->getLongitude(), $cityResult->getLongitude());
        }
    }

    public function testHttpException()
    {
        $mockSerializer = $this->createMock(SerializerInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $browser = $this->createMock(Browser::class);
        $exception = new Exception('Some error');

        $browser
            ->expects($this->once())
            ->method('get')
            ->willReturn(reject($exception));

        $musementApi = new MusementApi($browser, $mockSerializer, $mockLogger);
        $result = $musementApi->getCities();

        $this->assertPromiseFailsWithException($result, AppException::class);
    }
}
