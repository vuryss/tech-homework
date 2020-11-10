<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AppException;
use App\Service\CityWeatherForecast;
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
        try {
            $this->outputCityForecast($output);
        } catch (AppException $exception) {
            $output->writeln('');
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('<error>One or more errors occurred during processing of city forecasts</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @throws AppException
     *
     * @param OutputInterface $output
     */
    private function outputCityForecast(OutputInterface $output): void
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
                throw new AppException('Missing forecast for city: ' . $city->getName());
            }

            $output->writeln('');
        }
    }
}
