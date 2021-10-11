<?php

namespace App\Command;

use App\GoogleAPI\GoogleSheets;
use App\Routes\TaxiRouteList as YandexTaxiRouteList;
use App\SQLite\SQLiteConnection;
use App\YandexAPI\YandexWeatherAPI;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function env;

class YandexTaxiList extends Command
{
    protected static $defaultName = 'yandextaxi:get-data';

    protected function configure()
    {
        $this
            ->setDescription('Получает данные из Яндекс.Такси')
            ->setHelp(
                'Используется для получения текущей стоимости поездки по выбранному направлению'
            );

        $this
            ->addOption(
                'direction',
                'd',
                InputOption::VALUE_REQUIRED,
                'Направление поездки',
                'to_work'
            );
    }
//
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = new Logger('cli');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/logger.log', Logger::DEBUG));

        $io = new SymfonyStyle($input, $output);
        $direction = $input->getOption('direction');

        $io->title($this->getDescription());
        $io->text($this->getHelp());
        $io->newLine();

        $sql = new SQLiteConnection();
        $pdo = $sql->connect();

        $yandex_taxi = new YandexTaxiRouteList('Route_list_for_IB_Project');
        $yandex_taxi->setGoogleSheets(
            GoogleSheets::getService(),
            env('GOOGLE_SPREADSHEET_ID'),
            env('GOOGLE_SPREADSHEET_ROUTE_RANGE'),
            env('GOOGLE_SPREADSHEET_RESULT_RANGE')
        );
        $yandex_taxi->setWeatherData(new YandexWeatherAPI(
            env('YANDEX_WEATHER_URI_API'),
            env('YANDEX_WEATHER_API_KEY'),
            [
                'long' => env('WEATHER_COORDINATE_LONGITUDE'),
                'lat' => env('WEATHER_COORDINATE_LATITUDE')
            ]
        ));
        if ($yandex_taxi->loadFromGoogleSheet())
        {
            if ($yandex_taxi->requestTaxiPrice() and $yandex_taxi->requestWeather())
            {
                //var_dump($yandex_taxi->getWeatherData()->getResultArray());
                $yandex_taxi->writeResultToDB($pdo);
                $yandex_taxi->saveToGoogleSheet();
                $yandex_taxi->writeResultToGoogleSheet([
                    'id',
                    'name',
                    'description',
                    'price_date',
                    'price_time',
                    'price_currency',
                    'route_distance',
                    'route_time',
                    'route_econom_price',
                    'route_business_price',
                    'route_comfortplus_price']);
            }
        } else {
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}