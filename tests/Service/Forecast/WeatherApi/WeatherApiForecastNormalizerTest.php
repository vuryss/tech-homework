<?php

declare(strict_types=1);

namespace App\Tests\Service\Forecast\WeatherApi;

use App\Service\Forecast\Forecast;
use App\Service\Forecast\WeatherApi\WeatherApiForecastNormalizer;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class WeatherApiForecastNormalizerTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testValidData()
    {
        $date = new DateTimeImmutable('now');

        $forecast = (new Forecast())
            ->setWeather('Some weather')
            ->setDate($date);

        $apiResponseDecoded = [
            'forecast' => [
                'forecastday' => [
                    0 => [
                        'day' => [
                            'condition' => [
                                'text' => $forecast->getWeather(),
                            ],
                        ],
                        'hour' => [
                            0 => [
                                'time_epoch' => $forecast->getDate()->getTimestamp(),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $mockLogger = $this->createMock(LoggerInterface::class);

        $normalizer = new WeatherApiForecastNormalizer($mockLogger);
        $result = $normalizer->denormalize($apiResponseDecoded, Forecast::class, 'json');

        if ($result instanceof Generator) {
            $resultForecast = $result->current();

            $this->assertEquals($forecast->getDate()->getTimestamp(), $resultForecast->getDate()->getTimestamp());
            $this->assertEquals($forecast->getWeather(), $resultForecast->getWeather());
        }
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

        $normalizer = new WeatherApiForecastNormalizer($mockLogger);
        $result = $normalizer->denormalize($data, Forecast::class, 'json');

        if ($result instanceof Generator) {
            $result->current();
        }
    }

    public function invalidDataProvider()
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
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                                'hour' => [
                                    0 => [
                                        'time_epoch' => 'asd',
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
                                'day' => [
                                    'condition' => [
                                        'text' => 'Some weather',
                                    ],
                                ],
                                'hour' => [
                                    0 => [
                                        'time_epoch' => -123132,
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
                                'day' => [
                                ],
                                'hour' => [
                                    0 => [
                                        'time_epoch' => 1604966400,
                                    ],
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
                                'day' => [
                                    'condition' => [
                                        'text' => ['Some weather'],
                                    ],
                                ],
                                'hour' => [
                                    0 => [
                                        'time_epoch' => 1604966400,
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
