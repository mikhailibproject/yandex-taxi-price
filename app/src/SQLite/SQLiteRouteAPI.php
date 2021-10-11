<?php

namespace App\SQLite;

use PDO;

class SQLiteRouteAPI
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param PDO $pdo SQLite database connector
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int $route_id Route Id
     * @return mixed if exist return Id of record else "false"
     */
    public function isRouteExist(int $route_id)
    {
        $stmt = $this->pdo->prepare('select id from routes where route_id = :route_id');
        $stmt->bindValue(':route_id', $route_id);
        $stmt->execute();
        //print_r($stmt->fetch(PDO::FETCH_BOTH));
        return $stmt->fetchColumn();
    }

    /**
     * @param array $route_data array with data
     * @return int return inserted route record ID or false
     */
    public function insertRoute(array $route_data): int
    {
        $stmt = $this->pdo->prepare(
            'insert into routes (route_id, name, description, from_lat, from_long, to_lat, to_long, request_count)
            values (:route_id, :name, :description, :from_lat, :from_long, :to_lat, :to_long, :request_count);');
        $this->setRouteParam($stmt, $route_data);
        $stmt->execute();
        return $this->pdo->lastInsertId('id');
    }

    /**
     * @param array $route_data
     * @return bool
     */
    public function updateRoute(array $route_data): bool
    {
        $stmt = $this->pdo->prepare(
        'update routes 
        set name=:name, 
            description=:description, 
            from_lat=:from_lat, 
            from_long=:from_long, 
            to_lat=:to_lat, 
            to_long=:to_long, 
            request_count=:request_count 
        where route_id=:route_id;');
        $this->setRouteParam($stmt, $route_data);
        return $stmt->execute();

    }

    /**
     * @param int $route_id
     * @param array $route_data
     * @param int $weather_id
     * @return int
     */
    public function insertWeather(string $req_date, array $weather_data): int
    {
        $stmt = $this->pdo->prepare(
            'insert into weather (datetime, temp, feels_like, condition, wind_speed, wind_gust, wind_dir, 
                     pressure_mm, pressure_pa, humidity, daytime, season, obs_time)
            values (:datetime, :temp, :feels_like, :condition, :wind_speed, :wind_gust, :wind_dir, 
                    :pressure_mm, :pressure_pa, :humidity, :daytime, :season, :obs_time);');
        $stmt->bindValue(':datetime', $req_date);
        $stmt->bindValue(':temp', $weather_data['fact']['temp']);
        $stmt->bindValue(':feels_like', $weather_data['fact']['feels_like']);
        $stmt->bindValue(':condition', $weather_data['fact']['condition']);
        $stmt->bindValue(':wind_speed', $weather_data['fact']['wind_speed']);
        $stmt->bindValue(':wind_gust', $weather_data['fact']['wind_gust']);
        $stmt->bindValue(':wind_dir', $weather_data['fact']['wind_dir']);
        $stmt->bindValue(':pressure_mm', $weather_data['fact']['pressure_mm']);
        $stmt->bindValue(':pressure_pa', $weather_data['fact']['pressure_pa']);
        $stmt->bindValue(':humidity', $weather_data['fact']['humidity']);
        $stmt->bindValue(':daytime', $weather_data['fact']['daytime']);
        $stmt->bindValue(':season', $weather_data['fact']['season']);
        $stmt->bindValue(':obs_time', $weather_data['fact']['obs_time']);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId('id');
    }


    /**
     * @param int $route_id
     * @param array $route_data
     * @param int $weather_id
     * @return int
     */
    public function insertReqPrice(int $route_id, array $route_data, int $weather_id): int
    {
        $stmt = $this->pdo->prepare(
            'insert into req_price (route_id, datetime, currency, distance, duration_time, weather_id)
            values (:route_id, :datetime, :currency, :distance, :duration_time, :weather_id);');
        $stmt->bindValue(':route_id', $route_id, PDO::PARAM_INT);
        $stmt->bindValue(':datetime', $route_data['price_date'] . "T" . $route_data['price_time']);
        $stmt->bindValue(':currency', $route_data['price_array']['currency']);
        $stmt->bindValue(':distance', round($route_data['price_array']['distance']));
        $stmt->bindValue(':duration_time', (int)$route_data['price_array']['time']);
        $stmt->bindValue(':weather_id', $weather_id);
        $stmt->execute();
        return (int)$this->pdo->lastInsertId('id');
    }

    /**
     * @param int $req_id
     * @param array $route_data
     * @return int
     */
    public function insertPrices(int $req_id, array $route_data): int
    {
        foreach ($route_data['price_array']['options'] as $option) {
            $stmt = $this->pdo->prepare(
                'insert into prices (req_id, price, min_price, waiting_time, class_name, class_level)
            values (:req_id, :price, :min_price, :waiting_time, :class_name, :class_level);');
            $stmt->bindValue(':req_id', $req_id, PDO::PARAM_INT);
            $stmt->bindValue(':price', (key_exists('price', $option) ? $option['price'] : ''));
            $stmt->bindValue(':min_price', (key_exists('min_price', $option) ? $option['min_price'] : ''));
            $stmt->bindValue(':waiting_time', (key_exists('waiting_time', $option) ? (int)$option['waiting_time'] : ''));
            $stmt->bindValue(':class_name', (key_exists('class_name', $option) ? $option['class_name'] : ''));
            $stmt->bindValue(':class_level', (key_exists('class_level', $option) ? $option['class_level'] : ''));
            $stmt->execute();
        }
        return (int)$this->pdo->lastInsertId('id');

    }

    /**
     * @param $stmt
     * @param array $route_data
     */
    public function setRouteParam($stmt, array $route_data): void
    {
        $stmt->bindValue(':route_id', (int)$route_data['id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $route_data['name']);
        $stmt->bindValue(':description', $route_data['description']);
        $stmt->bindValue(':from_lat', $route_data['from']['lat']);
        $stmt->bindValue(':from_long', $route_data['from']['long']);
        $stmt->bindValue(':to_lat', $route_data['to']['lat']);
        $stmt->bindValue(':to_long', $route_data['to']['long']);
        $stmt->bindValue(':request_count', $route_data['request_count']);
    }
}