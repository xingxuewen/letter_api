<?php

namespace App\Services\Core\Platform\Kami\Kami\Config;

/**
 * 卡秘-卡秘配置
 * Class Config
 * @package App\Services\Core\Platform\Kami\Kami\Config
 */
class Config
{
    // 域名 http://test-main.pcuion.com:8081
    const DOMAIN = PRODUCTION_ENV ? 'https://main.kamicredit.com' : 'https://main.kamicredit.com';

    // URI
    // 联登接口
    const LOGIN_URI = '/api.php/CreditPage/unionlogins';

    //联登地址
    public static function getLoginUrl()
    {
        return static::DOMAIN . static::LOGIN_URI;
    }

    // 平台号order_id
    const ORDER_ID = PRODUCTION_ENV ? '20181008sdapp4953A00F=' : '20181008sdapp4953A00F=';

    // 渠道号channel_id
    const CHANNEL_ID = PRODUCTION_ENV ? 'sdappunion' : 'sdappunion';
}