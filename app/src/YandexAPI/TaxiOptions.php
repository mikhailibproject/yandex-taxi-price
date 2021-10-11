<?php

namespace App\YandexAPI;

/**
 * Taxi options enumerator
 */
abstract class TaxiOptions
{
    const Yellowcarnumber     = 'yellowcarnumber'    ;   // — машина с желтыми номерами.
    const Nosmoking           = 'nosmoking'          ;   // — некурящий водитель.
    const Childchair          = 'childchair'         ;   // — наличие детского кресла в машине.
    const Bicycle             = 'bicycle'            ;   // — перевозка велосипеда.
    const Conditioner         = 'conditioner'        ;   // — кондиционер в машине.
    const Animaltransport     = 'animaltransport'    ;   // — перевозка животных.
    const Universal           = 'universal'          ;   // — машина-универсал.
    const Check               = 'check'              ;   // — необходима квитанция об оплате.
    const Ski                 = 'ski'                ;   // — перевозка лыж или сноуборда.
    const Waiting_in_transit  = 'waiting_in_transit' ;   // — ожидание в пути.
    const Meeting_arriving    = 'meeting_arriving'   ;   // — встреча с табличкой.
    const Luggage             = 'luggage'            ;   // — платная перевозка багажа.
}