<?php

declare(strict_types=1);

namespace App\Tests\Service\Forecast\WeatherApi;

use App\Service\Forecast\Forecast;
use App\Service\Forecast\WeatherApi\WeatherApiForecastNormalizer;
use App\Service\Musement\City;
use DateTimeImmutable;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class WeatherApiForecastNormalizerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testValidData()
    {
        $date = new DateTimeImmutable('now');

        $forecast = (new Forecast())
            ->setWeather('Some weather')
            ->setDate($date);

        $apiResponseDecoded = [
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
        ];

        $mockLogger = $this->createMock(LoggerInterface::class);

        $normalizer = new WeatherApiForecastNormalizer($mockLogger);
        $resultForecast = $normalizer->denormalize($apiResponseDecoded, Forecast::class, 'json');

        $this->assertEquals($forecast->getDate()->getTimestamp(), $resultForecast->getDate()->getTimestamp());
        $this->assertEquals($forecast->getWeather(), $resultForecast->getWeather());
    }

    public function testSupports()
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $normalizer = new WeatherApiForecastNormalizer($mockLogger);
        $this->assertTrue($normalizer->supportsDenormalization([], Forecast::class));
        $this->assertFalse($normalizer->supportsDenormalization([], City::class));
    }

    /**
     * @throws Exception
     *
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
            $result->next();
        }
    }

    public function invalidDataProvider()
    {
        return [
            'Missing Day' => [
                [
                    'hour' => [
                        0 => [
                            'time_epoch' => 123456,
                        ],
                    ],
                ],
            ],
            'Missing Hour' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => 'Some weather',
                        ],
                    ],
                ],
            ],
            'Missing Hour 0' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => 'Some weather',
                        ],
                    ],
                    'hour' => [],
                ],
            ],
            'Missing timestamp' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => 'Some weather',
                        ],
                    ],
                    'hour' => [
                        0 => [],
                    ],
                ],
            ],
            'Missing weather text' => [
                [
                    'day' => [
                        'condition' => [],
                    ],
                    'hour' => [
                        0 => [
                            'time_epoch' => 123456,
                        ],
                    ],
                ],
            ],
            'Invalid Timestamp' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => 'Some weather',
                        ],
                    ],
                    'hour' => [
                        0 => [
                            'time_epoch' => -123456,
                        ],
                    ],
                ],
            ],
            'Invalid Timestamp 2' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => 'Some weather',
                        ],
                    ],
                    'hour' => [
                        0 => [
                            'time_epoch' => 'asdds',
                        ],
                    ],
                ],
            ],
            'Invalid Condition' => [
                [
                    'day' => [
                        'condition' => [
                            'text' => ['Some weather'],
                        ],
                    ],
                    'hour' => [
                        0 => [
                            'time_epoch' => -123456,
                        ],
                    ],
                ],
            ],
        ];
    }
}
