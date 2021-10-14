<?php

namespace App\Routes;

use App\YandexAPI;
use App\YandexAPI\TaxiClass;
use App\YandexAPI\TaxiOptions;

class TaxiRoute
{
    private $id;
    private $name = '';
    private $description = '';
    private $from = ['long' => '', 'lat' => ''];
    private $to   = ['long' => '', 'lat' => ''];
    private $class_array = [];
    private $option_array = [];
    private $price_date;
    private $price_time;
    private $price_data = [];
    private $request_count = 0;
    private $error_message = '';
    /**
     * @param string $name Name
     * @param string $desc Description
     * @param array|string[] $from from Array [long,lat]
     * @param array|string[] $to   to Array [long,lat]
     * @param string $id  record id
     */
    public function __construct(string $name, string $desc = '',
                                array  $from = ['long' => '', 'lat' => ''],
                                array  $to   = ['long' => '', 'lat' => ''],
                                string $id = '',
                                int $request_count = 0,
                                array $class_list = [TaxiClass::Econom, TaxiClass::Comfort, TaxiClass::ComfortPlus],
                                array $option_list = [TaxiOptions::Nosmoking]
    )
    {
        $this->name = $name;
        $this->description = $desc;
        $this->from['long'] = $from['long'];
        $this->from['lat']  = $from['lat'];
        $this->to['long'] = $to['long'];
        $this->to['lat']  = $to['lat'];
        $this->id = $id;
        $this->request_count = $request_count;
        $this->class_array = $class_list;
        $this->option_array = $option_list;
    }

    public function requestYandexPrice()
    {
        $yandex_price = new YandexAPI\YandexTaxiPrice($this->from, $this->to, $this->class_array, $this->option_array);
        $this->price_date = date('Y-m-d');
        $this->price_time = date('H:i:sP');
        $this->error_message = '';
        $yandex_price->requestData();
        $this->error_message = $yandex_price->getErrorMessage();
        if (empty($this->error_message)){
            $this->price_data = $yandex_price->getResultArray();
            $this->request_count++;
        } else
        {
            $this->price_data = [];
        }
    }

    /**
     * @return string
     */
    public function getPriceData(): array
    {
        return array(
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'from'          => $this->from,
            'to'            => $this->to,
            'class_array '  => $this->class_array,
            'option_array'  => $this->option_array,
            'price_date'    => $this->price_date,
            'price_time'    => $this->price_time,
            'request_count' => $this->request_count,
            'error_message' => $this->error_message,
            'price_array'   => $this->price_data);
    }



    public function getPriceArray(): array
    {
        $data = $this->initPriceArray();
        $data['id'            ]   = $this->id;
        $data['name'          ]   = $this->name;
        $data['description'   ]   = $this->description;
        $data['from_long'     ]   = $this->from['long'];
        $data['from_lat'      ]   = $this->from['lat'];
        $data['to_long'       ]   = $this->to['long'];
        $data['to_lat'        ]   = $this->to['lat'];
        $data['price_date'    ]   = $this->price_date;
        $data['price_time'    ]   = $this->price_time;
        $data['request_count' ]   = $this->getRequestCount();
        $data['error_message' ]   = $this->getErrorMessage();

        if (!empty($this->price_data)) {
            $data['price_currency'] = (key_exists('currency', $this->price_data) ? $this->price_data['currency'] : '');
            $data['route_distance'] = (key_exists('distance', $this->price_data) ? $this->price_data['distance'] : '');
            $data['route_time']     = (key_exists('time', $this->price_data) ? $this->price_data['time'] : '');

            foreach ($this->price_data['options'] as $option) {
                $data['route_' . $option['class_name'] . '_price'] =
                    (key_exists('price', $option) ? $option['price'] : '');
                $data['route_' . $option['class_name'] . '_min_price'] =
                    (key_exists('min_price', $option) ? $option['min_price'] : '');
                $data['route_' . $option['class_name'] . '_waiting_time'] =
                    (key_exists('waiting_time', $option) ? $option['waiting_time'] : '');
                $data['route_' . $option['class_name'] . '_class_name'] =
                    (key_exists('class_name', $option) ? $option['class_name'] : '');
                $data['route_' . $option['class_name'] . '_class_level'] =
                    (key_exists('class_level', $option) ? $option['class_level'] : '');
            }
        }
        return $data;
    }

    public function initPriceArray() : array
    {
        $data = array();
        $data['id'            ]   = '';
        $data['name'          ]   = '';
        $data['description'   ]   = '';
        $data['from_long'     ]   = '';
        $data['from_lat'      ]   = '';
        $data['to_long'       ]   = '';
        $data['to_lat'        ]   = '';
        $data['price_date'    ]   = '';
        $data['price_time'    ]   = '';
        $data['price_currency']   = '';
        $data['route_distance']   = '';
        $data['route_time'    ]   = '';
        $data['request_count' ]   = '';
        $data['error_message' ]   = '';
        //$timezones = DateTimeZone::listAbbreviations();
        foreach ($this->class_array as $class_name)
        {
            $data['route_' . $class_name . '_price'       ]    = '';
            $data['route_' . $class_name . '_min_price'   ]    = '';
            $data['route_' . $class_name . '_waiting_time']    = '';
            $data['route_' . $class_name . '_class_name'  ]    = '';
            $data['route_' . $class_name . '_class_level' ]    = '';
        }
        return $data;
    }

    public function getPriceValues(array $key_list = []): array
    {
        $result = array();
        if (empty($key_list))
        {
            return array_values($this->getPriceArray());
        } else
        {
            $data = $this->getPriceArray();
            foreach ($key_list as $key)
            {
                if (key_exists($key, $data))
                {
                    $result[] = $data[$key];
                } else {
                    $result[] = '';
                }
            }
        }
        return $result;
    }

    public function getPriceKeys(): array
    {
        return array_keys($this->getPriceArray());
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getRequestCount(): int
    {
        return $this->request_count;
    }

    /**
     * @param int $request_count
     */
    public function setRequestCount(int $request_count): void
    {
        $this->request_count = $request_count;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }
}