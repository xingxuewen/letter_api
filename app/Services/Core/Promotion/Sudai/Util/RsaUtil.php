<?php

namespace App\Services\Core\Promotion\Sudai\Util;

class RsaUtil
{
    private $public_key = ''; //公钥
    private $private_key = ''; //私钥
    public static $util; //单例对象

    /**
     * 单例构造
     * @return static
     */
    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }

        return self::$util;
    }

    /**
     * 构造方法
     * RsaUtil constructor.
     */
    function __construct()
    {
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : '');
        $this->private_key = file_get_contents($str . 'rsa_private_key.pem', 1);
        $this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
    }

    /** rsa加密算法
     * @param $data
     * @return string
     */
    public function rsaEncrypt($data)
    {
        //公钥内容
        $public_content = $this->public_key;
        //判断公钥的可用性
        $public_key = openssl_get_publickey($public_content);
        //公钥加密
        $res = $this->encrypt($data);
        return $res ? $res : '';
    }

    /** 分段加密
     * @param $originalData
     * @return string
     */
    private function encrypt($originalData)
    {
        $crypto = '';
        $chunks = str_split($originalData, 117);
        foreach ($chunks as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->public_key);
            $crypto .= $encryptData;
        }

        return base64_encode($crypto);
    }


    /**
     * 私钥解密
     *
     * @param string $data
     * @return string
     */
    public function privateDecrypt($data = '')
    {
        //私钥内容
        $key_content = $this->private_key;
        //判断公钥的可用性
        $key_content = openssl_get_privatekey($key_content);
        if (!$key_content) return false;
        //公钥加密
        $res = $this->decrypt($data);
        return $res ? $res : '';
    }

    /**
     * 分段解密
     *
     * @param string $encrypted
     * @return string
     */
    private function decrypt($encrypted = '')
    {
        $encrypted = base64_decode($encrypted);
        $crypto = '';
        foreach (str_split($encrypted, 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $this->private_key);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    //加密码时把特殊符号替换成URL可以带的内容
    private function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    //解密码时把转换后的符号替换特殊符号
    private function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
}