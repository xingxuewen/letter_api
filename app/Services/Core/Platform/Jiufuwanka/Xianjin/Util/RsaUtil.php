<?php
namespace App\Services\Core\Platform\Jiufuwanka\Xianjin\Util;
use Mockery\Exception;

/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-6-28
 * Time: 下午7:12
 */
class RsaUtil {

    private $public_key = ''; // 公钥
    private $private_key = ''; // 私钥
    public static $util;      // 单例对象

    /** 单例构造
     * @return static
     */
    public static function i()
    {
        if (!(self::$util instanceof static))
        {
            self::$util = new static();
        }

        return self::$util;
    }

    /** 构造方法
     * RsaUtil constructor.
     */
    function __construct()
    {
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : 'uat_');
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
    private function encrypt($originalData){
        $crypto = '';
        $chunks = str_split($originalData, 117);
        foreach ($chunks as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->public_key);
            $crypto .= $encryptData;
        }

        return base64_encode($crypto);
    }
}