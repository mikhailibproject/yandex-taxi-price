<?php

namespace App\Command;

use App\SQLite\SQLiteConnection;
use App\SQLite\SQLiteRouteReport;
use App\YandexAPI\TaxiClass;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YandexTaxiReportStatistics extends Command
{
    protected static $defaultName = 'yandextaxi:statistics';

    protected function configure()
    {
        $this
            ->setDescription('Yandex.Taxi and Yandex.Weather requested data report')
            ->setHelp(
                'Options: -o, --output   CSV filename to saving reported data'
            );
        $this
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'Path name for CSV files. Default - current dir',
                dirname(__FILE__)
            );
        $this
            ->addOption(
                'date_begin',
                'b',
                InputOption::VALUE_REQUIRED,
                'Begin Learning period - date YYYY-MM-DD',
                '1900-01-01'
            );
        $this
            ->addOption(
                'date_end',
                'e',
                InputOption::VALUE_REQUIRED,
                'End Learning period - date YYYY-MM-DD',
                '2021-11-17'
            );
        $this
            ->addOption(
                'date_begin_check',
                'c',
                InputOption::VALUE_REQUIRED,
                'End checking period - date YYYY-MM-DD',
                '2021-11-18'
            );
        $this
            ->addOption(
                'date_end_check',
                't',
                InputOption::VALUE_REQUIRED,
                'End checking period - date YYYY-MM-DD',
                '2099-12-31'
            );
        $this
            ->addOption(
                'statistic_measure',
                'm',
                InputOption::VALUE_REQUIRED,
                'Statistics measure name stdiv - standard deviation',
                'stdiv'
            );
        $this
            ->addOption(
                'statistic_period',
                'p',
                InputOption::VALUE_REQUIRED,
                'Calculate statistic measure fo date, week, month, year, all',
                'date'
            );


    }
//
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        function writeReportCSV(string $outfile, object $report)
        {
            $of = fopen($outfile, "w") or die("Unable to create file!");
            $r_count = 0;
            if ($row = $report->fetch()) {
                fputcsv($of, array_keys($row));
                fputcsv($of, $row);
                $r_count++;
            }
            while ($row = $report->fetch())
            {
                fputcsv($of, $row);
                $r_count++;
            }
            fclose($of);
            return $r_count;
        }

        $log = new Logger('cli');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/logger.log', Logger::DEBUG));

        $io = new SymfonyStyle($input, $output);
        $output_path    = $input->getOption('output');
        $date_begin = $input->getOption('date_begin');
        $date_end   = $input->getOption('date_end');
        $date_begin_check = $input->getOption('date_begin_check');
        $date_end_check    = $input->getOption('date_end_check');
        $statistics_period = $input->getOption('statistic_period');
        $statistic_measure = $input->getOption('statistic_measure');

        $io->title($this->getDescription());
        // $io->text($this->getHelp());
        $io->newLine();

        //Creating DB connection
        $sql = new SQLiteConnection();
        $pdo = $sql->connect();
        $report = new SQLiteRouteReport($pdo);

        // Writing data to files by taxi car classes
        foreach ( [TaxiClass::Econom, TaxiClass::Comfort, TaxiClass::ComfortPlus] as $class_name )
        {
            // Learning data report
            $outfile = $output_path . '/'. 'st_'. $statistic_measure .'_'. $statistics_period . '_learn_' . $class_name
                       . '_' . $date_begin . '_' . $date_end . '.csv';
            switch ($statistics_period) {
                case 'date':
                    $report->getStatisticsStDevDate($class_name, $date_begin, '00:00:00', $date_end, '23:59:59');
                    break;
                case 'week':
                    $report->getStatisticsStDevWeek($class_name, $date_begin, '00:00:00', $date_end, '23:59:59');
                    break;
                case 'all':
                    $report->getStatisticsStDevAll($class_name, $date_begin, '00:00:00', $date_end, '23:59:59');
                    break;
            }
            $io->text("Start writing data to the file:" . $outfile);
            $io->text("Written " . writeReportCSV($outfile, $report) . " line(s)");

            // Checking data report
            $outfile = $output_path . '/'. 'st_'. $statistic_measure .'_'. $statistics_period . '_check_' . $class_name
                       . '_' . $date_begin_check . '_' . $date_end_check . '.csv';
            switch ($statistics_period) {
                case 'date':
                    $report->getStatisticsStDevDate($class_name, $date_begin_check, '00:00:00', $date_end_check, '23:59:59');
                    break;
                case 'week':
                    $report->getStatisticsStDevWeek($class_name, $date_begin_check, '00:00:00', $date_end_check, '23:59:59');
                    break;
                case 'all':
                    $report->getStatisticsStDevAll($class_name, $date_begin_check, '00:00:00', $date_end_check, '23:59:59');
                    break;
            }
            $io->text("Start writing data to the file:" . $outfile);
            $io->text("Written " . writeReportCSV($outfile, $report) . " line(s)");
        }

        $io->newLine();


        return Command::SUCCESS;
    }

}