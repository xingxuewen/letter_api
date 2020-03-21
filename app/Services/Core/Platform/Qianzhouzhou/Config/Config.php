<?php

namespace App\Services\Core\Platform\Qianzhouzhou\Config;

/**
 * 钱周周配置
 * Class Config
 * @package App\Services\Core\Platform\Qianzhouzhou
 */
class Config
{
    // 域名
    const DOMAIN = PRODUCTION_ENV ? 'http://47.92.25.43:8081' : 'http://47.92.67.80:8081';
    // URI
    const NO_REPEAT_URI = '/core/platform/checkDuplicate';// 用户排重（撞库）验证接口
    const SELECT_URI = '/core/platform/getUserInfo'; // 查询接口

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


    // 合作机构ID
    const PARTNER_ID = PRODUCTION_ENV ? 'sdzjApp' : 'sdzjApp';     // 钱周周为友商提供的id
    // 渠道号
    const CHANNEL_NO = PRODUCTION_ENV ? 'SDZJ' : 'SDZJ';

}