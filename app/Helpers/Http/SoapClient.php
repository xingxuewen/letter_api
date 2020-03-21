<?php

namespace App\Helpers\Http;

use SoapClient as Soap;

/**
 * @author zhaoqiying
 */
class SoapClient
{

    private static $client;
    private static $config = [];

    public static function i($wsdlUrl = '')
    {
        if (!(self::$client instanceof Soap)) {

            self::$client = new Soap($wsdlUrl);
        }

        return self::$client;
    }

}
