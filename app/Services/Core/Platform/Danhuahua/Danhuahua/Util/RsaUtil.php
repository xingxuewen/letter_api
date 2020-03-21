<?php

namespace App\Services\Core\Platform\Danhuahua\Danhuahua\Util;

use App\Services\Core\Platform\Danhuahua\Danhuahua\Config\Config;

/**
 * 抖贝数据处理
 *
 * Class RsaUtil
 * @package App\Services\Core\Platform\Doubei\Doubei
 */
class RsaUtil
{
    public static $util;
    private $public_key = ''; //公钥
    private $private_key = ''; //私钥

    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }

        return self::$util;
    }

    /** 构造方法
     * RsaUtil constructor.
     */
    private function __construct()
    {
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : 'uat_');

    }

    /**
     * 公钥加密
     *
     * @param array $datas
     * @return null|string
     */
    public function getPublicSign($datas = [])
    {
        $signKey = $datas['ua'] . $datas['key'] . $datas['ua'];
        return $signKey ? $signKey : "";
    }

    /**
     * 用md5加密参数
     *
     * @param string $param
     * @return string
     */
    public function encryptParamByMd5($param = '')
    {
        $sign = Config::SDZJ_DOUBEI_SIGN;
        $param = md5($param) . $sign;

        return $param ? md5($param) : '';
    }
}