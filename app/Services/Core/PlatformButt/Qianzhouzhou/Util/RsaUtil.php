<?php

namespace App\Services\Core\PlatformButt\Qianzhouzhou\Util;

/**
 * 钱周周
 * Class RsaUtil
 * @package App\Services\Core\Platform\Qianzhouzhou\Util
 */
class RsaUtil
{
    private $public_key = ''; //公钥
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
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : 'uat_');
        $this->private_key = file_get_contents($str . 'rsa_private_key.pem', 1);
//        $this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
    }

    /**
     * 签名
     * @param array $datas
     * @return null|string
     */
    public function getSign($datas = [])
    {
        //验证数组
        if (!is_array($datas)) {
            return null;
        }
        //参数排序
        $data = $this->buildQuery($datas);
        //私钥内容
        $private_content = $this->private_key;
        //判断私钥的可用性
        $private_key = openssl_pkey_get_private($private_content);
        //私钥加密
        openssl_sign($data, $sign, $private_key);
        openssl_free_key($private_key);
        $sign = base64_encode($sign);

        return $sign ? $sign : '';
    }


    /**
     * 数据格式处理
     * @param array $datas
     * @return null|string
     */
    public function buildQuery($datas = [])
    {
        if (!$datas) {
            return null;
        }
        //将要 参数 排序
        ksort($datas);
        //重新组装参数
        $params = array();
        foreach ($datas as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        $data = implode('&', $params);
        return $data;
    }
}