<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\AppException;
use App\Service\CityWeatherForecast;
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

    public function __construct(LoopInterface $loop, CityWeatherForecast $cityWeatherForecast, LoggerInterface $logger)
    {
        parent::__construct();

        $this->loop = $loop;
        $this->cityWeatherForecast = $cityWeatherForecast;
        $this->logger = $logger;
        $this->result = Command::SUCCESS;
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
        $days = $input->getOption('forecastDays');
        $days = ctype_digit($days) ? (int) $days : 2;

        if ($days < 1) {
            $output->writeln('');
            $output->writeln('<error>Cannot fetch forecast for less than 1 days</error>');
            return Command::FAILURE;
        }

        $this->outputCityForecastForDays($output, $days);

        $this->loop->run();

        return $this->result;
    }

    /**
     * @param int             $days
     * @param OutputInterface $output
     */
    private function outputCityForecastForDays(OutputInterface $output, int $days): void
    {
        $this
            ->cityWeatherForecast
            ->getCitiesWithForecastForDays($days)
            ->then(
                function (iterable $cities) use ($output, $days) {
                    foreach ($cities as $city) {
                        $forecastTexts = [];

                        foreach ($city->getForecasts() as $forecast) {
                            $forecastTexts[$forecast->getDate()->format('Ymd')] = $forecast->getWeather();
                        }

                        if (count($forecastTexts) !== $days) {
                            throw new AppException('Missing one or more forecasts for city: ' . $city->getName());
                        }

                        ksort($forecastTexts);

                        $output->writeln(
                            'Processed city ' . $city->getName()
                            . ' | ' . implode(' - ', $forecastTexts)
                        );
                    }

                    $this->result = Command::SUCCESS;
                }
            )
            ->then(
                null,
                function (Exception $exception) use ($output) {
                    $output->writeln('');
                    $output->writeln('<error>' . $exception->getMessage() . '</error>');
                    $output->writeln('<error>One or more errors occurred during processing of city forecasts</error>');
                    $this->result = Command::FAILURE;
                }
            );
    }
}
