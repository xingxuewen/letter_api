<?php

namespace App\Services\Core\Platform\Jielebao\Util;

use App\Services\Core\Platform\Jielebao\Config\Config;
/**
 * 借乐宝
 * Class RsaUtil
 * @package App\Services\Core\Platform\Jietiao\Suijiesuihua\Util
 */
class AesUtil
{
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
        $this->key=PRODUCTION_ENV ? '6SW3SLDKKSAS3AFJ': '7BFCF5C921231SDF';
    }

    public function encode($datas=[]){
        $key=$this->key;
        //验证数组
        $string = json_encode($datas);
        $iv = $password = substr(md5($key),0,16);//AES算法的密码password和初始变量iv
        $encrypted = openssl_encrypt($string, 'AES-128-CBC',$password,1,$iv);
        $en_result = base64_encode($encrypted); //bizData 密文数据
        return $en_result;
    }
    /**
     * 签名
     * @param array $datas
     * @return null|string
     */
    public function getSign($params = array())
    {
        $key=$this->key;
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

        return md5($srcStr.$key);
    }
}