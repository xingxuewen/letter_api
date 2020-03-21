<?php

namespace App\Services\Core\PlatformButt\DanHuahua\DanHuahua\Config;

/**
 * 蛋花花配置
 *
 * Class Config
 * @package App\Services\Core\Platform\Doubei\Doubei
 */
class Config
{
    // 域名
//    const DOMAIN = PRODUCTION_ENV ? 'https://openapi.jindanfenqi.com' : 'http://39.106.98.57:8080';
    const DOMAIN = PRODUCTION_ENV ? 'http://39.106.98.57:8080' : 'https://openapi.jindanfenqi.com';
    // 用户排重（撞库）验证接口
    const NO_REPEAT_URI = '/api/partner/impactCertification';
    // 查询接口
    const SELECT_URI = '';
    // 联登接口
    const LOGIN_URI = '/federatedLogin';

    const UA = "PARTNER-SUDAIZHIJIA";

//    const KEY =  PRODUCTION_ENV ? "91eeb2b81f73447280aa3ae5d0f52924" : "ffa6a79d65e3484bbbbba59a0dd7a358";
    const KEY =  PRODUCTION_ENV ? "ffa6a79d65e3484bbbbba59a0dd7a358" : "91eeb2b81f73447280aa3ae5d0f52924";
    const SOURCE = "924";


    //排重(撞库)地址
    public static function getNorepeatUrl()
    {
        return static::DOMAIN . static::NO_REPEAT_URI;
    }

    //查询地址
    public static function getSelectUrl()
    {
        return static::DOMAIN . static::SELECT_URI;
    }

    //联登地址
    public static function getLoginUrl()
    {
        return static::DOMAIN . static::LOGIN_URI;
    }

    //手机号加密签名
    const SDZJ_DOUBEI_SIGN = 'Danhuahua-crm08atB';

    // 合作机构ID
    const PARTNER_ID = PRODUCTION_ENV ? 'sdzj0814' : 'ceshi';
    // 渠道号
    const CHANNEL_NO = PRODUCTION_ENV ? 'SDZJ' : 'SDZJ';
}