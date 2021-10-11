<?php

namespace App\Routes;

use App\GoogleAPI\GoogleSheets;
use App\SQLite\SQLiteRouteAPI;
use Google\Auth\Cache\InvalidArgumentException;
use Google\Service\Sheets;
use PDO;
use RuntimeException;


class TaxiRouteList
{
    private $name;
    private $routes = [];
    private $route_count = 0;
    private $google_sheets;
    private $spreadsheetId;
    private $range_to_read;
    private $read_header = [];
    private $range_to_write;
    private $write_header = [];
    private $weather_data;
    //private $db;
    private $loaded = false;
    private $requested = false;
    private $written = false;
    private $saved = false;


    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @param TaxiRoute $route
     */
    public function addRoute(TaxiRoute $route)
    {
        $this->routes[] = $route;
        $this->route_count++ ;
        $this->requested = false;
        $this->saved = false;
    }
    public function clearRoutes()
    {
        unset($this->routes);
        $this->route_count = 0;
        $this->loaded = false;
        $this->requested = false;
        $this->saved = false;
    }

    /**
     * @param mixed $google_sheets
     */
    public function setGoogleSheets(Sheets $google_sheets,
                                    string $spreadsheetId,
                                    string $range_to_read,
                                    string $range_to_write): void
    {
        if (!empty($this->google_sheets))  unset($this->google_sheets);
        $this->google_sheets = $google_sheets;
        $this->spreadsheetId = $spreadsheetId;
        $this->range_to_read = $range_to_read;
        $this->range_to_write = $range_to_write;
    }

    /**
     *
     */
    public function loadFromGoogleSheet(): bool
    {
        try {
        $response = $this->google_sheets->spreadsheets_values->get($this->spreadsheetId, $this->range_to_read);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage());
        }
        $values = $response->getValues();
        if (empty($values)) {
            return false;
        }
        // First row is header(column names)
        if (empty(array_diff(['id','name','description', 'from_long', 'from_lat', 'to_long', 'to_lat','request_count', 'error_message' ], $values[0])))
        {
            $this->read_header = $values[0];
            unset($values[0]);
            foreach ($values as $row)
            {
                $this->addRoute(new TaxiRoute(
                    $row[array_search('name',$this->read_header)],
                    $row[array_search('description',$this->read_header)],
                    [   'long' => $row[array_search('from_long',$this->read_header)],
                        'lat' =>  $row[array_search('from_lat',$this->read_header)]],
                    [   'long' => $row[array_search('to_long',$this->read_header)],
                        'lat' =>  $row[array_search('to_lat',$this->read_header)]],
                    $row[array_search('id',$this->read_header)],
                    (int) $row[array_search('request_count',$this->read_header)]
                ));
            }

        } else {

            return false;
        }
        $this->loaded = true;
        return true;
    }
    public function saveToGoogleSheet(): bool
    {
        if (empty($this->read_header))
            $this->read_header = ['id',
                'name',
                'description',
                'from_long',
                'from_lat',
                'to_long',
                'to_lat',
                'request_count',
                'error_message' ];
        $values = [];
        $values[] = $this->read_header;
        foreach ($this->routes as $route)
        {
            $values[] = $route->getPriceValues($this->read_header);
        }
        $requestBody = GoogleSheets::makeValueRange($this->range_to_read, $values);
        try {
            $response = $this->google_sheets->spreadsheets_values->update($this->spreadsheetId,
                $this->range_to_read, $requestBody, ['valueInputOption' => 'USER_ENTERED']);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage());
        }
        $this->saved = true;
        return true;
    }

    public function writeResultToGoogleSheet( array $write_header = [] ): bool
    {
        if (!empty($write_header)) $this->write_header = $write_header;
        if (empty($this->write_header)) {
            if (!empty($this->routes))
                //Get header from first route
                $this->write_header = $this->routes[0]->getPriceKeys();
        }
        $values = [];
        $values[] = $this->write_header;
        foreach ($this->routes as $route)
        {
            $values[] = $route->getPriceValues($this->write_header);
        }
        $requestBody = GoogleSheets::makeValueRange($this->range_to_write, $values);
        try {
            $response = $this->google_sheets->spreadsheets_values->update($this->spreadsheetId,
                $this->range_to_write, $requestBody, ['valueInputOption' => 'USER_ENTERED']);
        } catch (InvalidArgumentException $e) {
            throw new RuntimeException($e->getMessage());
        }
        $this->written = true;
        return true;
    }

    public function writeResultToDB(PDO $pdo): bool
    {
        $route_db = new SQLiteRouteAPI($pdo);
        $weather_id = $route_db->insertWeather(date("c"),$this->weather_data->getResultArray());
        foreach ($this->routes as $route)
        {
            $values = $route->getPriceData();
            $route_id = $route_db->isRouteExist((int)$values['id']);
            if ($route_id) {
                if ($route_db->updateRoute($values))
                    print_r('Update route ' . $route_id . " - " . "\n");
            } else {
                $route_id = $route_db->insertRoute($values);
                print_r('Insert route ' . $route_id . "\n");
            }
            if ($weather_id) {
                print_r('Weather id ' . $weather_id . "\n");

                $req_id = $route_db->insertReqPrice($route_id, $values, $weather_id);
                if ($req_id) {
                    $route_db->insertPrices($req_id, $values);
                }
            }
        }

        return true;
    }

    public function getResultAsArray( array $header = [], bool $add_header = true): array
    {
        if (empty($header))
            if (!empty($this->routes))
                //Get header from first route
                $header = $this->routes[0]->getPriceKeys();

        $values = [];
        if ($add_header) $values[] = $header;
        foreach ($this->routes as $route)
        {
            $values[] = $route->getPriceValues($header);
        }
        return $values;
    }


    public function requestTaxiPrice(): bool
    {
        //var_dump($this->routes);
        //if (!$this->isLoaded()) $this->loadFromGoogleSheet();
        if (!empty($this->routes))
        {
            foreach ($this->routes as $route)
            {
                $route->requestYandexPrice();
            }

        } else {
            return false;
        }
        $this->requested = true;
        return true;
    }
    public function requestWeather(): bool
    {
        if (!empty($this->weather_data))
        {
            print_r("requesting Weather... \n");
            $this->weather_data->requestData();
            //print_r($this->weather_data->getErrorMessage());


        } else {
            return false;
        }
        //var_dump($this->weather_data->getResultArray());
        if (!empty($this->weather_data->getResultArray()))
            return true;
        else
            return false;
}

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * @return bool
     */
    public function isSaved(): bool
    {
        return $this->saved;
    }

    /**
     * @return bool
     */
    public function isRequested(): bool
    {
        return $this->requested;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isWritten(): bool
    {
        return $this->written;
    }

    /**
     * @return mixed
     */
    public function getWeatherData()
    {
        return $this->weather_data;
    }

    /**
     * @param mixed $weather_data
     */
    public function setWeatherData($weather_data): void
    {
        $this->weather_data = $weather_data;
    }
}