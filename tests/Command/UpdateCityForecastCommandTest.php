<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\UpdateCityForecastCommand;
use App\Exception\AppException;
use App\Service\CityWeatherForecast;
use App\Service\Musement\City;
use App\Service\Weather\Forecast;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateCityForecastCommandTest extends KernelTestCase
{
    public function testCityForecast()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $mockCityWeatherForecast = $this->createMock(CityWeatherForecast::class);
        $logger                  = $this->createMock(LoggerInterface::class);

        $application->add(new UpdateCityForecastCommand($mockCityWeatherForecast, $logger));
        $command = $application->find('app:update-city-forecast');
        $commandTester = new CommandTester($command);

        $mockCityWeatherForecast
            ->method('getCitiesWithForecast')
            ->willReturn(
                [
                    (new City())
                        ->setName('Test City')
                        ->addForecast(
                            (new Forecast())
                                ->setDate(new DateTimeImmutable('now'))
                                ->setWeather('Test weather today')
                        )
                        ->addForecast(
                            (new Forecast())
                                ->setDate(new DateTimeImmutable('tomorrow'))
                                ->setWeather('Test weather tomorrow')
                        )
                ]
            );

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        $this->assertNotEmpty($commandOutput);
        $this->assertEquals('Processed city Test City | Test weather today - Test weather tomorrow', $commandOutput);
    }

    public function testCityForecastWithoutTomorrow()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $mockCityWeatherForecast = $this->createMock(CityWeatherForecast::class);
        $logger                  = $this->createMock(LoggerInterface::class);

        $application->add(new UpdateCityForecastCommand($mockCityWeatherForecast, $logger));
        $command = $application->find('app:update-city-forecast');
        $commandTester = new CommandTester($command);

        $mockCityWeatherForecast
            ->method('getCitiesWithForecast')
            ->willReturn(
                [
                    (new City())
                        ->setName('Test City')
                        ->addForecast(
                            (new Forecast())
                                ->setDate(new DateTimeImmutable('now'))
                                ->setWeather('Test weather today')
                        )
                ]
            );

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        $this->assertNotEmpty($commandOutput);
        $this->assertEquals('Processed city Test City | Test weather today', $commandOutput);
    }

    public function testCityMissingForecast()
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $mockCityWeatherForecast = $this->createMock(CityWeatherForecast::class);
        $logger                  = $this->createMock(LoggerInterface::class);

        $application->add(new UpdateCityForecastCommand($mockCityWeatherForecast, $logger));
        $command = $application->find('app:update-city-forecast');
        $commandTester = new CommandTester($command);

        $mockCityWeatherForecast
            ->method('getCitiesWithForecast')
            ->willReturn(
                [
                    (new City())
                        ->setName('Test City')
                ]
            );

        $commandTester->execute([]);

        $commandOutput = trim($commandTester->getDisplay());

        $expectedOutput = 'Processed city Test City' . PHP_EOL
            . 'Missing forecast for city: Test City' . PHP_EOL
            . 'One or more errors occurred during processing of city forecasts';

        $this->assertEquals($expectedOutput, $commandOutput);
    }
}
