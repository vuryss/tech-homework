<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CityWeatherForecast;
use App\Service\Musement\MusementApiInterface;
use App\Service\Weather\WeatherApiInterface;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCityForecastCommand extends Command
{
    protected static $defaultName = 'app:update-city-forecast';

    private CityWeatherForecast $cityWeatherForecast;
    private LoggerInterface $logger;

    public function __construct(CityWeatherForecast $cityWeatherForecast, LoggerInterface $logger)
    {
        parent::__construct();

        $this->cityWeatherForecast = $cityWeatherForecast;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->cityWeatherForecast->getCitiesWithForecast() as $city) {
            $output->write('Processed city ' . $city->getName());

            $todayForecast = $city->getForecastForDay(new DateTimeImmutable('now'));

            if ($todayForecast) {
                $output->write(' | ' . $todayForecast->getWeather());

                $tomorrowForecast = $city->getForecastForDay(new DateTimeImmutable('tomorrow'));

                if ($tomorrowForecast) {
                    $output->write(' - ' . $tomorrowForecast->getWeather());
                } else {
                    $this->logger->error('Missing tomorrow\'s forecast for city: ' . $city->getName());
                }
            } else {
                $this->logger->error('Missing forecast for city: ' . $city->getName());
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
