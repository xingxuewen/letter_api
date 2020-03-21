<?php

namespace App\Services\Core\PlatformButt\Doubei\Doubei\Config;

/**
 * 抖贝配置
 *
 * Class Config
 * @package App\Services\Core\Platform\Doubei\Doubei
 */
class Config
{
    // 域名
    const DOMAIN = PRODUCTION_ENV ? 'https://www.kuailaidai.com' : 'http://cavan.changhuahua.cn';
    // URI
    // 用户排重（撞库）验证接口
    const NO_REPEAT_URI = '/mapi/index.php?i_type=1&r_type=1&act=ajax&act_2=check_sdzj_user';
    // 查询接口
    const SELECT_URI = '';
    // 联登接口
    const LOGIN_URI = '/mapi/index.php?i_type=1&r_type=1&act=ajax&act_2=add_sdzj_user';


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
    const SDZJ_DOUBEI_SIGN = 'doubei-crm08atB';

    // 合作机构ID
    const PARTNER_ID = PRODUCTION_ENV ? 'sdzj0814' : 'ceshi';
    // 渠道号
    const CHANNEL_NO = PRODUCTION_ENV ? 'SDZJ' : 'SDZJ';
}