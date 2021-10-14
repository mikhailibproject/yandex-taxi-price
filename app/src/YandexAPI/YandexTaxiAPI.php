<?php

namespace App\YandexAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class to handle requests to the Yandex Taxi API service.
 *
 */

class YandexTaxiAPI
{
    private $base_url = '';
    private $payload_array = [];
    private $class_array = [];
    private $option_array = [];
    private $lang = '';
    private $clid = '';
    private $apikey = '';
    private $route =
        [  'from' =>
            ['long' => '', 'lat' => ''],
            'to' =>
            ['long' => '', 'lat' => '']
        ];
    private $response_json = '';
    private $result_array = [];
    private $requested = false;
    private $error_message = '';
    public $result = 0;

    /**
     * @param $url
     * @param $clid
     * @param $apikey
     * @param string[] $from
     * @param string[] $to
     * @param array $class_list
     * @param array $option_list
     * @param string $lang
     */
    public function __construct(string $url, string $clid, string $apikey,
                                array $from = ['long'=>'', 'lat' => ''],
                                array $to   = ['long'=>'', 'lat' => ''],
                                array $class_list = [TaxiClass::Econom],
                                array $option_list = [TaxiOptions::Nosmoking],
                                string $lang = 'ru')
    {
        $this->base_url = $url;
        $this->clid = $clid;
        $this->apikey = $apikey;
        $this->route['from']['long']    = $from['long'];
        $this->route['from']['lat']     = $from['lat'];
        $this->route['to']['long']      = $to['long'];
        $this->route['to']['lat']       = $to['lat'];
        $this->option_array = $option_list;
        $this->class_array = $class_list;
        $this->lang = $lang;
        $this->initPayload();
    }

    private function initPayload (): void
    {
        $this->payload_array = [
            'clid' => $this->clid,
            'class' => implode(",", $this->class_array),
            'req' => implode(",", $this->option_array),
            'rll' => $this->route['from']['long'] . ','. $this->route['from']['lat'] . '~' .
                     $this->route['to']['long'] . ','.   $this->route['to']['lat'],
            'lang' => $this->lang
        ];
        $this->requested = false;
    }

    /**
     * @param string[][] $route
     * String array coordinate from-to.
     */
    public function setRoute(array $route): void
    {
        $this->route = $route;
        $this->initPayload();
    }

    /**
     * @return bool
     */
    public function isRequested(): bool
    {
        return $this->requested;
    }

    /**
     * @return string[][]
     */
    public function getRoute(): array
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getResponseJson(): string
    {
        return $this->response_json;
    }

    /**
     * @return array
     */
    public function getResultArray(): array
    {
        return $this->result_array;
    }

    public function requestData(): object
    {
        $response = null;
        $http = new Client(
            [   'base_url' => $this->base_url,
                'headers' => [
                    'Accept' => 'application/json',
                    'YaTaxi-Api-Key' => $this->apikey
                ]
            ]
        );
        //print_r($this->payload_array);
        try {
            $response = $http->get($this->base_url, ['query' => $this->payload_array]);
        } catch (GuzzleException $e) {
            $this->error_message = $e->getMessage();
            // print_r($this->error_message);
            // throw new \DomainException('Yandex Taxi API GET error: ' . $e->getMessage());
        }
        if (!empty($response)) {
            $this->response_json = (string)$response->getBody();
            $this->result_array = json_decode($this->response_json, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $this->error_message = $this->error_message . 'Yandex Taxi response json parse error. ';
                //throw new \DomainException('Yandex Taxi response json parse error');
            }
            $this->requested = true;
            return $response;
        } else {
            $this->response_json = '';
            $this->result_array = [];
        }
        return (object) NULL;
    }

    /**
     * @return array
     */
    public function getOptionArray(): array
    {
        return $this->option_array;
    }


    /**
     * @return array
     */
    public function getClassArray(): array
    {
        return $this->class_array;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

}