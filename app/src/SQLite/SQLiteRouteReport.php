<?php

namespace App\SQLite;

use PDO;
use PDOStatement;


class SQLiteRouteReport
{
    /**
     * @var PDO
     */
    private $pdo;
    /**
     * @var PDOStatement
     */
    private $stmt;
    /**
     * @param PDO $pdo SQLite database connector
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllData()
    {
        $this->stmt = $this->pdo->prepare(
            "SELECT
            r.route_id as id,
            r.name AS name,
            r.description AS description,
            date(rp.datetime, 'localtime') AS price_date,
            time(rp.datetime, 'localtime') as price_time,
            strftime('%w', rp.datetime, 'localtime' ) as price_weekday,
            strftime('%H', rp.datetime, 'localtime' ) * 60 +
            strftime('%M', rp.datetime, 'localtime' ) as price_minutes_after_midnight,
            rp.distance as route_distance,
            rp.duration_time as route_time,
            waiting_time,
            rp.currency as currency,
            price,
            class_name,
            class_level,
            w.temp as w_temp,
            w.feels_like as w_feels_like,
            w.condition as w_condition,
            w.daytime as w_daytime,
            w.wind_speed as w_wind_speed,
            w.wind_dir as w_wind_dir,
            w.pressure_mm as w_pressure_mm,
            w.humidity as w_humidity,
            w.daytime as w_daytime,
            w.season as w_season
            FROM
            prices p
            INNER JOIN req_price rp on p.req_id = rp.id
            INNER JOIN weather w on w.id = rp.weather_id
            INNER JOIN routes r on rp.route_id = r.id;"
        );
        //$stmt->bindValue(':route_id', $route_id);
        return $this->stmt->execute();
    }

    public function getFilteredData(string $class_name,
                                    string $date_begin, string $time_begin,
                                    string $date_end, string $time_end)
    {
        $this->stmt = $this->pdo->prepare(
            "SELECT
            r.route_id as id,
            r.name AS name,
            r.description AS description,
            date(rp.datetime, 'localtime') AS price_date,
            time(rp.datetime, 'localtime') as price_time,
            strftime('%w', rp.datetime, 'localtime' ) as price_weekday,
            strftime('%H', rp.datetime, 'localtime' ) * 60 +
            strftime('%M', rp.datetime, 'localtime' ) as price_minutes_after_midnight,
            rp.distance as route_distance,
            rp.duration_time as route_time,
            waiting_time,
            rp.currency as currency,
            price,
            class_name,
            class_level,
            w.temp as w_temp,
            w.feels_like as w_feels_like,
            w.condition as w_condition,
            w.daytime as w_daytime,
            w.wind_speed as w_wind_speed,
            w.wind_dir as w_wind_dir,
            w.pressure_mm as w_pressure_mm,
            w.humidity as w_humidity,
            w.daytime as w_daytime,
            w.season as w_season
            FROM
            prices p
            INNER JOIN req_price rp on p.req_id = rp.id
            INNER JOIN weather w on w.id = rp.weather_id
            INNER JOIN routes r on rp.route_id = r.id
            WHERE p.class_name = $class_name AND
                  datetime(rp.datetime, 'localtime') >= $date_begin . ' ' . $time_begin AND 
                  datetime(rp.datetime, 'localtime') <= $date_end . ' ' . $time_end;"
        );
        //$stmt->bindValue(':route_id', $route_id);
        return $this->stmt->execute();

    }

    /**
     * @return PDOStatement
     */
    public function getStmt()
    {
        return $this->stmt;
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
}