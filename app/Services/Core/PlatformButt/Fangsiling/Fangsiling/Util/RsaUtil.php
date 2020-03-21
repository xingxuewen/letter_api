<?php

namespace App\Services\Core\PlatformButt\Fangsiling\Fangsiling\Util;

/**
 * 房司令
 * Class RsaUtil
 * @package App\Services\Core\Platform\Fangsiling\Util
 */
class RsaUtil
{
    private $private_key = ''; //私钥
    public static $util;


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
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : '');
        $this->private_key = file_get_contents($str . 'rsa_private_key.pem', 1);
    }

    /**
     * private签名
     * @param array $datas
     * @return null|string
     */
    public function getSign($params = [])
    {
        //验证数组
        if (!is_array($params)) {
            return null;
        }
        //参数排序
        $srcStr = "";
        $names = array();
        foreach($params as $name => $value) {
            $names[$name] = $name;
        }
        sort($names);
        foreach($names as $name) {
            $srcStr = $srcStr.$name."=".$params[$name]."&";
        }
        $srcStr = substr($srcStr, 0, strlen($srcStr) - 1);
        //私钥内容
        $private_content = $this->private_key;
        //判断私钥的可用性
        $private_key = openssl_pkey_get_private($private_content);
        //私钥加密
        $signature = '';

        openssl_sign($srcStr, $signature, $private_key, OPENSSL_ALGO_SHA1);
        openssl_free_key($private_key);

        $sign = base64_encode($signature);
        //dd($sign);
        return $sign ? $sign : '';
    }
}