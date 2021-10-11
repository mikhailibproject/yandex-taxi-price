<?php

namespace App\Command;

use App\SQLite\SQLiteConnection;
use App\SQLite\SQLiteRouteReport;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YandexTaxiReport extends Command
{
    protected static $defaultName = 'yandextaxi:report';

    protected function configure()
    {
        $this
            ->setDescription('Отчет о ценах YandexTaxi')
            ->setHelp(
                'Help...'
            );
        $this
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'имя файла для выгрузки отчета. По умолчанию на экран',
                'stdout'
            );
    }
//
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = new Logger('cli');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/logger.log', Logger::DEBUG));

        $io = new SymfonyStyle($input, $output);
        $outfile = $input->getOption('output');

        $io->title($this->getDescription());
        $io->text($this->getHelp());
        $io->newLine();
        $io->text($outfile);
        $io->newLine();


        $of = fopen($outfile, "w") or die("Unable to open file!");

        $sql = new SQLiteConnection();
        $pdo = $sql->connect();
        $report = new SQLiteRouteReport($pdo);
        $report->getAllData();

        if ($row = $report->fetch()) {
            $io->text(implode("|",array_keys($row)));
            fputcsv($of, array_keys($row));
            $io->text(implode("|",$row));
            fputcsv($of, $row);
        }

        while ($row = $report->fetch())
        {
            $io->text(implode("|",$row));
            fputcsv($of, $row);
            //$io->newLine();
        }
        fclose($of);

        return Command::SUCCESS;
    }

}