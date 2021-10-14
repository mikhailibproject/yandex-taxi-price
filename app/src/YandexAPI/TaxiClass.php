<?php

namespace App\YandexAPI;

/**
 * Taxi Class enumerator
 */
abstract class TaxiClass
{
    const Econom        = 'econom';         // «Эконом».
    const Comfort       = 'business';       // «Комфорт».
    const ComfortPlus   = 'comfortplus';    // «Комфорт+».
    const Minivan       = 'minivan';        // «Минивен».
    const Business      = 'vip';            // «Бизнес».
    const Express       = 'express';        // «Доставка».
    const Courier       = 'courier';        // «Курьер».
}
