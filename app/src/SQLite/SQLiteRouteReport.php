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
        // Add stdev aggregate function
        $this->pdo->sqliteCreateAggregate('stdev',
            function (&$context, $row, $data) // step callback
            {
                if (isset($context) !== true) // $context is null at first
                {
                    $context = array
                    (
                        'k' => 0,
                        'm' => 0,
                        's' => 0,
                    );
                }

                if (isset($data) === true) // the standard is non-NULL values only
                {
                    $context['s'] += ($data - $context['m']) * ($data - ($context['m'] += ($data - $context['m']) / ++$context['k']));
                }

                return $context;
            },
            function (&$context, $row) // fini callback
            {
                if ($context['k'] > 1) // return NULL if no non-NULL values exist
                {
                    return sqrt($context['s'] / ($context['k'] - 1));
                }

                return null;
            },
            1);


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
            strftime('%W', rp.datetime, 'localtime' ) as price_weeknumber,
            strftime('%m', rp.datetime, 'localtime' ) as price_month,
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
            WHERE p.class_name = :class_name AND
                  datetime(rp.datetime, 'localtime') >= :datetime_begin AND 
                  datetime(rp.datetime, 'localtime') <= :datetime_end;"
        );
        $this->stmt->bindValue(':class_name', $class_name);
        $this->stmt->bindValue(':datetime_begin', $date_begin . ' ' . $time_begin);
        $this->stmt->bindValue(':datetime_end', $date_end . ' ' . $time_end);
        //$stmt->bindValue(':route_id', $route_id);
        return $this->stmt->execute();

    }
    public function getStatisticsStDevDate(string $class_name,
                                           string $date_begin, string $time_begin,
                                           string $date_end, string $time_end
                                    )
    {

        $this->stmt = $this->pdo->prepare(
            "SELECT
            r.route_id as id,
            r.name AS name,
            date(rp.datetime, 'localtime') AS price_date,
            stdev(price) as price_stdev,     
            min(price) as price_min,
            max(price) as price_max,
            avg(price) as price_avg,
            class_name
            FROM
            prices p
            INNER JOIN req_price rp on p.req_id = rp.id
            INNER JOIN routes r on rp.route_id = r.id 

            WHERE p.class_name = :class_name AND
                  datetime(rp.datetime, 'localtime') >= :datetime_begin AND 
                  datetime(rp.datetime, 'localtime') <= :datetime_end
            GROUP BY r.route_id, r.name, date(rp.datetime, 'localtime'), class_name;"
        );
        $this->stmt->bindValue(':class_name', $class_name);
        $this->stmt->bindValue(':datetime_begin', $date_begin . ' ' . $time_begin);
        $this->stmt->bindValue(':datetime_end', $date_end . ' ' . $time_end);
        //$stmt->bindValue(':route_id', $route_id);
        return $this->stmt->execute();

    }

    public function getStatisticsStDevWeek(string $class_name,
                                           string $date_begin, string $time_begin,
                                           string $date_end, string $time_end
    )
    {

        $this->stmt = $this->pdo->prepare(
            "SELECT
            r.route_id as id,
            r.name AS name,
            strftime('%W', rp.datetime, 'localtime' ) as price_weeknumber,
            stdev(price) as price_stdev,
            min(price) as price_min,
            max(price) as price_max,
            avg(price) as price_avg,
            class_name
            FROM
            prices p
            INNER JOIN req_price rp on p.req_id = rp.id
            INNER JOIN routes r on rp.route_id = r.id 

            WHERE p.class_name = :class_name AND
                  datetime(rp.datetime, 'localtime') >= :datetime_begin AND 
                  datetime(rp.datetime, 'localtime') <= :datetime_end
            GROUP BY r.route_id, r.name, strftime('%W', rp.datetime, 'localtime' ), class_name;"
        );
        $this->stmt->bindValue(':class_name', $class_name);
        $this->stmt->bindValue(':datetime_begin', $date_begin . ' ' . $time_begin);
        $this->stmt->bindValue(':datetime_end', $date_end . ' ' . $time_end);
        //$stmt->bindValue(':route_id', $route_id);
        return $this->stmt->execute();

    }

    public function getStatisticsStDevAll(string $class_name,
                                           string $date_begin, string $time_begin,
                                           string $date_end, string $time_end
    )
    {

        $this->stmt = $this->pdo->prepare(
            "SELECT
            r.route_id as id,
            r.name AS name,
            stdev(price) as price_stdev,     
            min(price) as price_min,
            max(price) as price_max,
            avg(price) as price_avg,
            class_name
            FROM
            prices p
            INNER JOIN req_price rp on p.req_id = rp.id
            INNER JOIN routes r on rp.route_id = r.id 

            WHERE p.class_name = :class_name AND
                  datetime(rp.datetime, 'localtime') >= :datetime_begin AND 
                  datetime(rp.datetime, 'localtime') <= :datetime_end
            GROUP BY r.route_id, r.name, class_name;"
        );
        $this->stmt->bindValue(':class_name', $class_name);
        $this->stmt->bindValue(':datetime_begin', $date_begin . ' ' . $time_begin);
        $this->stmt->bindValue(':datetime_end', $date_end . ' ' . $time_end);
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