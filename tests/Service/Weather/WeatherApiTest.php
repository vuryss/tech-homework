<?php

/** @noinspection PhpUndefinedClassInspection */

declare(strict_types=1);

namespace App\Tests\Service\Weather;

use App\Exception\AppException;
use App\Service\Forecast\WeatherApi;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WeatherApiTest extends TestCase
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
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'date_epoch' => 1604966400,
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $musementApi = new WeatherApi($mockHttpClient, $mockLogger, 'api-key');
        $forecasts = $musementApi->getForecastByCoordinates('1.234', '5.678');

        foreach ($forecasts as $forecast) {
            $this->assertEquals('2020-11-10', $forecast->getDate()->format('Y-m-d'));
            $this->assertEquals('Some weather', $forecast->getWeather());
        }
    }

    /**
     * @throws AppException
     *
     * @param mixed $invalidResponse
     *
     * @dataProvider invalidResponseDataProvider
     */
    public function testInvalidResponse($invalidResponse)
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
            ->willReturn($invalidResponse);

        $musementApi = new WeatherApi($mockHttpClient, $mockLogger, 'api-key');
        $forecasts = $musementApi->getForecastByCoordinates('1.234', '5.678');

        if ($forecasts instanceof Generator) {
            $forecasts->current();
        }
    }

    public function invalidResponseDataProvider()
    {
        return [
            'Missing forecast' => [
                [['bla']],
            ],
            'Missing forecast day' => [
                [
                    'forecast' => [
                        'bla',
                    ],
                ],
            ],
            'Invalid forcast day' => [
                [
                    'forecast' => [
                        'forecastday' => 'bla',
                    ],
                ],
            ],
            'Missing timestamp' => [
                [
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid timestamp' => [
                [
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'date_epoch' => 'asd',
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid timestamp 2' => [
                [
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'date_epoch' => -654654,
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Missing condition' => [
                [
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'date_epoch' => 1604966400,
                                'day' => [
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'Invalid condition' => [
                [
                    'forecast' => [
                        'forecastday' => [
                            0 => [
                                'date_epoch' => 1604966400,
                                'day' => [
                                    'condition' => [
                                        'text' => ['Some weather'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
