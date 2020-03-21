<?php

namespace App\Services\Core\Platform\Rong360\Yuanzidai\Util;


/**
 * 原子贷
 * Class AesUtil
 * @package
 */
class AesUtil
{
    private $public_key = ''; //公钥
    private $private_key = ''; //私钥
    private $key='';
    private $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
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
        $this->key=PRODUCTION_ENV?'apepeb5u8gsdzj0l':'apepeb5u8gsdzj0l';
        //$this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
    }

    /**
     * 签名
     * @param array $datas
     * @return null|string
     */
    public  function getSign($strData)
    {
        if (empty($strData)) {
            return '';
        }
        //私钥，用于生成签名
        $signPrivateKey = $this->private_key;
        openssl_sign($strData, $sign, $signPrivateKey, OPENSSL_ALGO_SHA1);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 参数加密
     */
    public  function ascEncode($data)
    {
        $strData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $objCrypt =  base64_encode(openssl_encrypt($strData,"AES-128-CBC",$this->key,OPENSSL_RAW_DATA, $this->iv));
        return $objCrypt;
    }


    /**
     * 参数解密
     */
    public  function ascDecode($strData)
    {
        return openssl_decrypt(base64_decode($strData),"AES-128-CBC",$this->key,OPENSSL_RAW_DATA, $this->iv);
    }


}