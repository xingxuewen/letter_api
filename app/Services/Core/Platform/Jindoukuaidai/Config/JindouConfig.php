<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Platform\Jindoukuaidai\Config;

class JindouConfig {

    // 域名地址
    const DOMAIN = 'https://jin-api-t.51huaxin.cn';
    const URI = '/user/login';
    // medium
    const MEDIUM = '1010';
    // 加密私钥
    const KEY = '11d814daa7c4fa9541519223b7e798a8';

    public static function getUrl()
    {
        return static::DOMAIN . static::URI;
    }

}