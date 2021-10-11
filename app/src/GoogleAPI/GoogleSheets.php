<?php

namespace App\GoogleAPI;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class GoogleSheets
{
    public static function getService(){
        return new Sheets(self::getClient());
    }

    public static function getClient()
    {
        $client = new Client();
        $client->setApplicationName('Yandex Taxi Price IB Project');
        $client->addScope(Sheets::SPREADSHEETS);
        //$client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        //$client->useApplicationDefaultCredentials();
        $client->setAuthConfig(__DIR__ . '/../../storage/google-docs/ib-computer-science-project-2f8a0b2771c5.json');
        //$client->setAuthConfig(__DIR__ . '/../../storage/google-docs/client_secret_851209136691-chpgm23ce9pn4b6rei5t50mn0vogtcrg.apps.googleusercontent.com.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client;
    }

    public static function makeValueRange($range, $values): ValueRange
    {
        return new ValueRange(
            [
                'range' => $range,
                'majorDimension' => 'ROWS',
                'values' =>  $values,
            ]
        );
    }
}