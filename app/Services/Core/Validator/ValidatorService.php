<?php

/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-9-4
 * Time: 下午1:50
 */

namespace App\Services\Core\Validator;

use App\Services\AppService;

class ValidatorService extends AppService
{

    const SCORPIO_API_URL = 'https://api.51datakey.com';
    const TIANCHUANG_API_URL = 'http://api.tcredit.com';
    const ZHIMA_API_URL = 'https://zmopenapi.zmxy.com.cn/openapi.do';
    //face++
    const FACEID_API_URL = 'https://api.faceid.com';
    //face++ SDK3.0++
    const FACEID_API_URL_UPGRADE = 'https://api.megvii.com';

    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 天创tokenid
     *
     * @return string
     */
    public static function getTianChuangTokenid()
    {
        return '2f57cee2-9317-4f95-9e72-c4551fbfa3c7';
    }

    /**
     * 天创appid
     *
     * @return string
     */
    public static function getTianChuangAppid()
    {
        return 'b0a98c44-607d-4beb-97dd-a62abd738ce6';
    }

    /**
     * 魔蝎apikey
     *
     * @param string $apikey
     * @return string
     */
    public static function getScorpioApiKey($apikey = 'apikey')
    {
        return $apikey . ' a7d197f11ff54578800a0dfe76a5e648';
    }

    /**
     * 魔蝎token
     *
     * @param string $token
     * @return string
     */
    public static function getScorpioToken($token = 'token')
    {
        return $token . ' 5d10c2bbf76c4fa8b3761f2976035353';
    }

    /**
     * 魔蝎回调接口中hmacsha256的生成秘钥
     *
     * @return string
     */
    public static function getScorpioCallBackSecret()
    {
        return '27c7e4bc518c48d095d9caf544771876';
    }

    /**
     * 芝麻信用评分appid
     * @return string
     */
    public static function getZhimaCreditScoreAppId()
    {
        return '1004660';
    }

    /**
     * 芝麻信用评分产品代码
     * @return string
     */
    public static function getScoreProductCode()
    {
        return 'w1010100100000000001';
    }

    /**
     * 芝麻行业关注名单appid
     * @return string
     */
    public static function getZhimaCreditWatchlistAppId()
    {
        return '1004686';
    }

    /**
     * 行业关注名单产品代码
     * @return string
     */
    public static function getWatchlistProductCode()
    {
        return 'w1010100100000000022';
    }

    /**
     * face++ AppKey
     * @return string
     */
    public static function getFaceidAppKey()
    {
//        return PRODUCTION_ENV ? 'z81-ezAIUEUQcBI8RDtbcIDL1ENqxtRf' : 'Dbu3X9xgX6FRT6Ft7ymdH8DGbuTRVlen';
        return 'z81-ezAIUEUQcBI8RDtbcIDL1ENqxtRf';
    }

    /**
     * face++ AppSecret
     * @return string
     */
    public static function getFaceidAppSecret()
    {
//        return PRODUCTION_ENV ? '001Y-YSN4DDGauyux8OrrrLEIixo_2am' : 'nyg1gwC2p96Zf046huK8QttzxEpuBgQD';
        return '001Y-YSN4DDGauyux8OrrrLEIixo_2am';

    }

}
