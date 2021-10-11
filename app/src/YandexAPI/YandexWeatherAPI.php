<?php

namespace App\YandexAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class to handle requests to the Yandex Taxi API service.
 *
 */

class YandexWeatherAPI
{
    private $base_url = '';
    private $payload_array = [];
    private $lang = '';
    private $apikey = '';
    private $point = ['long' => '', 'lat' => ''];
    private $response_json = '';
    private $result_array = [];
    private $requested = false;
    private $error_message = '';
    public $result = 0;

    /**
     * @param $url
     * @param $apikey
     * @param string[] $point
     * @param string $lang
     */
    public function __construct(string $url, string $apikey,
                                array $point = ['long'=>'', 'lat' => ''],
                                string $lang = 'ru_RU')
    {
        $this->base_url = $url;
        $this->apikey = $apikey;
        $this->point['long']    = $point['long'];
        $this->point['lat']     = $point['lat'];
        $this->lang = $lang;
        $this->initPayload();
    }

    private function initPayload (): void
    {
        $this->payload_array = [
            'lat' => $this->point['lat'],
            'lon' => $this->point['long'],
            'lang' => $this->lang];
        $this->requested = false;
    }

    /**
     * @param string[][] $route
     * String array coordinate from-to.
     */
    public function setPoint(array $point): void
    {
        $this->point = $point;
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
                    'X-Yandex-API-Key' => $this->apikey
                ]
            ]
        );
        //var_dump($this->payload_array);
        //var_dump($this->apikey);
        try {
            $response = $http->get($this->base_url, ['query' => $this->payload_array]);
        } catch (GuzzleException $e) {
            print_r("Error... \n");
            $this->error_message = $e->getMessage();
        }
        if (!empty($response)) {
            $this->response_json = (string)$response->getBody();
            $this->result_array = json_decode($this->response_json, true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $this->error_message = $this->error_message . 'Yandex Weather response json parse error. ';
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
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

}