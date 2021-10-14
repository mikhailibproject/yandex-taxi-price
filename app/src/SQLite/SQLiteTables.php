<?php

namespace App\SQLite;

use PDO;

/**
 * SQLite Create Table Demo
 */
class SQLiteTables {

    /**
     * PDO object
     * @var PDO
     */
    private $pdo;

    /**
     * connect to the SQLite database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * create tables
     */
    public function createTables() {
        $commands =
            ['create table routes (
                id            integer
                    primary key autoincrement,
                route_id      integer not null,
                name          text,
                description   text,
                from_lat      real    not null,
                from_long     real    not null,
                to_lat        real    not null,
                to_long       real    not null,
               request_count integer default 0
            );',
            'create unique index routes_route_id_uindex
                on routes (route_id);',
            'create table if not exists req_price (
                id integer not null
                    constraint req_price_pk
                        primary key autoincrement,
                route_id integer not null
                    references routes (id) 
                        on update cascade on delete cascade,
                weather_id integer
                    references weather (id), 
                datetime text,
                currency text,
                distance real,
                duration_time integer
            );',
            'create table if not exists prices (
                id           integer
                    constraint prices_pk
                        primary key autoincrement,
                req_id       integer not null
                    references req_price (id)
                        on update cascade on delete cascade,
                price        integer,
                min_price    integer,
                waiting_time integer,
                class_name   text,
                class_level  integer
            );',
            'create table if not exists weather
            (
                id integer
                    constraint weather_pk
                        primary key autoincrement,
                datetime text,
                temp integer,
                feels_like integer,
                condition text,
                wind_speed integer,
                wind_gust integer,
                wind_dir text,
                pressure_mm integer,
                pressure_pa integer,
                humidity integer,
                daytime text,
                season text,
                obs_time integer
            );'
            ];
        // execute the sql commands to create new tables
        foreach ($commands as $command) {
            $this->pdo->exec($command);
        }
    }
    /**
     * drop tables
     */
    public function dropTables() {
        $commands = [
            'drop table IF EXISTS prices',
            'drop table IF EXISTS req_price',
            'drop table IF EXISTS weather',
            'drop table IF EXISTS routes'
        ];
        // execute the sql commands to drop tables
        foreach ($commands as $command) {
            $this->pdo->exec($command);
        }
    }


    /**
     * get the table list in the database
     */
    public function getTableList(): array {

        $stmt = $this->pdo->query("SELECT name
                                   FROM sqlite_master
                                   WHERE type = 'table'
                                   ORDER BY name");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[] = $row['name'];
        }

        return $tables;
    }

}