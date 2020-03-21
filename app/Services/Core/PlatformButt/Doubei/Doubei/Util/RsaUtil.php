<?php

namespace App\Services\Core\PlatformButt\Doubei\Doubei\Util;

use App\Services\Core\PlatformButt\Doubei\Doubei\Config\Config;

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
//        $this->private_key = file_get_contents($str . 'rsa_private_key.pem', 1);
        $this->public_key = file_get_contents($str . 'rsa_public_key.pem', 1);
    }

    /**
     * 公钥加密
     *
     * @param array $datas
     * @return null|string
     */
    public function getPublicSign($datas = [])
    {
        //验证数组
        if (!is_array($datas)) {
            return null;
        }
        //参数排序
//        $data = $this->buildQuery($datas);
        //公钥内容
        $public_content = $this->public_key;
        //判断公钥的可用性
        $public_key = openssl_get_publickey($public_content);
        //公钥加密
        $sign = $this->encrypt(json_encode($datas));

        return $sign ? $sign : '';
    }

    /** 分段加密
     * @param $originalData
     * @return string
     */
    private function encrypt($originalData){
        $crypto = '';
        $chunks = str_split($originalData, 117);
        foreach ($chunks as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->public_key);
            $crypto .= $encryptData;
        }

        return base64_encode($crypto);
    }

    /**
     * private签名
     * @param array $datas
     * @return null|string
     */
    public function getPrivateSign($datas = [])
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