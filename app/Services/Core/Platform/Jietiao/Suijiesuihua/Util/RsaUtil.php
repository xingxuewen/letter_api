<?php

namespace App\Services\Core\Platform\Jietiao\Suijiesuihua\Util;

/**
 * 借条-随借随花
 * Class RsaUtil
 * @package App\Services\Core\Platform\Jietiao\Suijiesuihua\Util
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
        //$this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
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
        //$data = $this->buildQuery($datas);
        //私钥内容
        $private_content = $this->private_key;
        //判断私钥的可用性
        $private_key = openssl_pkey_get_private($private_content);
        //私钥加密
        $json_str = json_encode($datas);

        $crypto = '';

        foreach (str_split($json_str, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encrypted, $private_content);
            $crypto .= $encrypted;
        }

        return $crypto ? base64_encode($crypto) : null;
    }
}