<?php

namespace App\Command;

use App\SQLite\SQLiteConnection;
use App\SQLite\SQLiteTables;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class YandexTaxiInit extends Command
{
    protected static $defaultName = 'yandextaxi:init';

    protected function configure()
    {
        $this
            ->setDescription('Создает или очищает базу данных о ценах YandexTaxi')
            ->setHelp(
                'Help...'
            );
    }
//
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $log = new Logger('cli');
        $log->pushHandler(new StreamHandler(__DIR__ . '/../../storage/logs/logger.log', Logger::DEBUG));

        $io = new SymfonyStyle($input, $output);

        $io->title($this->getDescription());
        $io->text($this->getHelp());
        $io->newLine();
        $sql = new SQLiteConnection();
        $pdo = $sql->connect();
        $tables = new SQLiteTables($pdo);
        $tables->dropTables();
        $tables->createTables();
        print_r($tables->getTableList());
        return Command::SUCCESS;
    }
}