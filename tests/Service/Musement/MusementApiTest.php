<?php
/** @noinspection PhpStatementHasEmptyBodyInspection */
/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Musement;

use App\Exception\AppException;
use App\Service\Musement\MusementApi;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $mockResponse
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(
                [
                    [
                        'name' => 'Test city',
                        'latitude' => 1.1234,
                        'longitude' => 5.6789,
                    ]
                ]
            );

        $musementApi = new MusementApi($mockHttpClient, $mockLogger);
        $cities = $musementApi->getCities();

        foreach ($cities as $city) {
            $this->assertEquals('Test city', $city->getName());
            $this->assertEquals('1.1234', $city->getLatitude());
            $this->assertEquals('5.6789', $city->getLongitude());
        }
    }

    /**
     * @param array $invalidResponse
     *
     * @dataProvider invalidResponseDataProvider
     */
    public function testInvalidResponse(array $invalidResponse)
    {
        $this->expectException(AppException::class);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $mockResponse
            ->expects($this->once())
            ->method('toArray')
            ->willReturn([$invalidResponse]);

        $musementApi = new MusementApi($mockHttpClient, $mockLogger);

        $cities = $musementApi->getCities();

        if ($cities instanceof Generator) {
            $cities->current();
        }
    }

    public function invalidResponseDataProvider()
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

    public function testHttpException()
    {
        $this->expectException(AppException::class);

        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        $mockHttpClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($this->createMock(HttpExceptionInterface::class));

        $musementApi = new MusementApi($mockHttpClient, $mockLogger);
        $cities = $musementApi->getCities();

        if ($cities instanceof Generator) {
            $cities->current();
        }
    }
}
