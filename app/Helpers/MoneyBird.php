<?php

namespace App\Helpers;

class Moneybird
{

    
    public static function getMoneybird(): \Picqer\Financials\Moneybird\Moneybird
    {
        $session = session();
        $connection = self::getConnection();
       
        $connection->setAccessToken($session->get('moneybird_access_token'));
        $connection->setAdministrationId($session->get('moneybird_administration_id'));

        $connection->connect();
        return new \Picqer\Financials\Moneybird\Moneybird($connection);
    }

    public static function getConnection(): \Picqer\Financials\Moneybird\Connection
    {
        $connection = new \Picqer\Financials\Moneybird\Connection();
        $connection->setRedirectUrl(env('MONEYBIRD_REDIRECT_URI'));
        $connection->setClientId(env('MONEYBIRD_CLIENT_ID'));
        $connection->setClientSecret(env('MONEYBIRD_CLIENT_SECRET'));
        return $connection;
    }

    public static function setAdministrationId(string $administrationId)
    {
        $session = session();
        $session->put('moneybird_administration_id', $administrationId);
    }

    public static function setAuthorizationCode(string $code)
    {
        $connection = self::getConnection();
        $connection->setAuthorizationCode($code);
        $connection->connect();

        $session = session();
        $accessToken = $connection->getAccessToken();
       
        $session->put('moneybird_access_token', $accessToken);
    }
}