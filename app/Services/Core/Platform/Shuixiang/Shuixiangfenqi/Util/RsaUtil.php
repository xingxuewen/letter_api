<?php
namespace App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\Util;

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
   //     $this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
    }
    /**
     * private签名
     * @param array $datas
     * @return null|string
     */
    public function getSign($json_str = '')
    {
        if (empty($json_str)) {
            return null;
        }
        //私钥内容
        $private_content = $this->private_key;
        //判断私钥的可用性
        $private_key = openssl_pkey_get_private($private_content);
        //私钥加密
        $signature = '';
        openssl_sign($json_str, $signature, $private_key, 'SHA256');
        openssl_free_key($private_key);
        $sign = base64_encode($signature);
        return $sign ? $sign : '';
    }

}