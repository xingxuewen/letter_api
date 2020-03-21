<?php

namespace App\Redis;

use Predis\Client;

class RedisClientFactory extends Client
{
    protected static $_obj = null;

    public function __construct(array $server = [])
    {
        if (empty($server)) {
            $server = [
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => env('REDIS_PORT', 6379),
                'database' => env('REDIS_DATABASE', 0),
            ];
            $password = env('REDIS_PASSWORD', '');
            if (!empty($password)) {
                $server['password'] = $password;
            }
        }

        return parent::__construct($server);
    }

    public static function get()
    {
        if (self::$_obj === null) {
            self::$_obj = new \Redis();
            self::$_obj->connect(env('REDIS_HOST'), env('REDIS_PORT'), 2);
            self::$_obj->auth(env('REDIS_PASSWORD'));
            self::$_obj->select(env('REDIS_DATABASE', 0));
        }

        return self::$_obj;
    }
}
