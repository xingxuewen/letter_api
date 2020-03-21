<?php

namespace App\Helpers;

class Encrypt
{
    /**
     * var string $method 加解密方法，可通过openssl_get_cipher_methods()获得
     */
    protected $method = 'AES-128-CBC';

    /**
     * var string $secret_key 加解密的密钥
     */
    protected $secret_key = '7RF92C2W49834DE9';

    /**
     * var string $iv 加解密的向量，有些方法需要设置比如CBC
     */
    protected $iv = 'D2FV2C2D49H94DEL';

    /**
     * var string $options （不知道怎么解释，目前设置为0没什么问题）
     */
    protected $options = OPENSSL_RAW_DATA;

    /**
     * 构造函数
     *
     * @param string $key 密钥
     * @param string $method 加密方式
     * @param string $iv iv向量
     * @param mixed $options 还不是很清楚
     *
     */
    public function __construct($key = '', $method = '', $iv = '', $options = null)
    {
        // key是必须要设置的
        $this->secret_key = $key ?: $this->secret_key;

        $this->method = $method ?: $this->method;

        $this->iv = $iv ?: $this->iv;

        $this->options = $options ?? $this->options;
    }

    /**
     * 加密方法，对数据进行加密，返回加密后的数据
     *
     * @param string $data 要加密的数据
     *
     * @return string
     *
     */
    public function encode($data)
    {
        $data = openssl_encrypt($data, $this->method, $this->secret_key, $this->options, $this->iv);
        return strtolower(bin2hex($data));
    }

    /**
     * 解密方法，对数据进行解密，返回解密后的数据
     *
     * @param string $data 要解密的数据
     *
     * @return string
     *
     */
    public function decode($data)
    {
        return openssl_decrypt(hex2bin($data), $this->method, $this->secret_key, $this->options, $this->iv);
    }
}