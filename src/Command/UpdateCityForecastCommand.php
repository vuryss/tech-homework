<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AppException;
use App\Service\CityWeatherForecast;
use App\Service\Musement\City;
use Exception;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCityForecastCommand extends Command
{
    protected static $defaultName = 'app:update-city-forecast';

    private LoopInterface $loop;
    private CityWeatherForecast $cityWeatherForecast;
    private LoggerInterface $logger;
    private int $result;
    private int $days;
    private OutputInterface $output;

    public function __construct(LoopInterface $loop, CityWeatherForecast $cityWeatherForecast, LoggerInterface $logger)
    {
        parent::__construct();

        $this->loop = $loop;
        $this->cityWeatherForecast = $cityWeatherForecast;
        $this->logger = $logger;
        $this->result = Command::SUCCESS;
        $this->days = 2;
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetches a list of cities from Musement API and their forecasts for 2 days.')
            ->addOption(
                'forecastDays',
                'd',
                InputOption::VALUE_REQUIRED,
                'Number of days to fetch Forecast for',
                2
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;

        $days = $input->getOption('forecastDays');
        $this->days = ctype_digit($days) ? (int) $days : 2;

        if ($this->days < 1) {
            $output->writeln('');
            $output->writeln('<error>Cannot fetch forecast for less than 1 days</error>');
            return Command::FAILURE;
        }

        $this->processCityForecast();

        $this->loop->run();

        return $this->result;
    }

    private function processCityForecast(): void
    {
        $this
            ->cityWeatherForecast
            ->getCitiesWithForecastForDays($this->days)
            ->then(fn ($cities) => $this->outputCities($cities))
            ->then(null, fn (Exception $exception) => $this->handleException($exception));
    }

    /**
     * @param City[] $cities
     *
     * @throws AppException
     */
    private function outputCities(iterable $cities): void
    {
        foreach ($cities as $city) {
            $forecastTexts = [];

            foreach ($city->getForecasts() as $forecast) {
                $forecastTexts[$forecast->getDate()->format('Ymd')] = $forecast->getWeather();
            }

            if (count($forecastTexts) !== $this->days) {
                throw new AppException('Missing one or more forecasts for city: ' . $city->getName());
            }

            ksort($forecastTexts);

            $this->output->writeln(
                'Processed city ' . $city->getName()
                . ' | ' . implode(' - ', $forecastTexts)
            );
        }

        $this->result = Command::SUCCESS;
    }

    private function handleException(Exception $exception): void
    {
        $this->output->writeln('');
        $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
        $this->output->writeln(
            '<error>One or more errors occurred during processing of city forecasts</error>'
        );
        $this->result = Command::FAILURE;
    }
}
