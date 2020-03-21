<?php

namespace App\Services\Core\Platform\Quhuafenqi\Config;

class Config
{
    // 域名
    const DOMAIN = PRODUCTION_ENV ? 'https://m.shoujidai.com' : 'https://m.shoujidai.com';

    // 联登接口
    const LOGIN_URI = '/shoujidai/openapi/1.0.0/account/oauth';

    const MERCHANTID = '201810231509';

    const PRIVATEKEY = 'private_key.pem';

    const DEFAULT_URL = 'https://m.shoujidai.com?channel=sudaizj-llcs';
    //联登地址
    public static function getLoginUrl()
    {
        return static::DOMAIN . static::LOGIN_URI;
    }
}