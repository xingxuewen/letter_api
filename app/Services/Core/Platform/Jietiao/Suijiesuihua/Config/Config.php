<?php

namespace App\Services\Core\Platform\Jietiao\Suijiesuihua\Config;

/***
 * 借条-随借随花配置
 * Class Config
 * @package App\Services\Core\Platform\Jietiao\Suijiesuihua\Config
 */
class Config
{
    // 域名
    const DOMAIN = PRODUCTION_ENV ? 'https://m.qianyijt.com' : 'http://uat.m.jietiao365.com';

    // URI
    // 用户排重（撞库）验证接口
    const NO_REPEAT_URI = '';
    // 查询接口
    const SELECT_URI = '';
    // 联登接口
    const LOGIN_URI = '/v1/oauth';


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

    //加密id
    const ECRYPT_ID = 'sudaizhijia';

    // 合作机构ID
    const PARTNER_ID = PRODUCTION_ENV ? 'jgqGD6' : 'ogEkGD';

}