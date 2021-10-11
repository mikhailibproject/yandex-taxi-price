<?php

namespace App\YandexAPI;

use function env;

class YandexTaxiPrice extends YandexTaxiAPI
{
    public function __construct(array $from = ['long' => '', 'lat' => ''],
                                array $to = ['long' => '', 'lat' => ''],
                                array $class_list = [TaxiClass::Econom, TaxiClass::Comfort, TaxiClass::ComfortPlus],
                                array $option_list = [TaxiOptions::Nosmoking],
                                string $lang = 'ru'
                                )
    {
        parent::__construct(
            env('YANDEX_TAXI_URI_API'),
            env('YANDEX_CLIENT_ID'),
            env('YANDEX_API_KEY'),
            $from,
            $to,
            $class_list,
            $option_list,
            $lang
         );
    }
}