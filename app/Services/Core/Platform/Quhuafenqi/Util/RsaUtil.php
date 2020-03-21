<?php

namespace App\Services\Core\Platform\Quhuafenqi\Util;

class RsaUtil{

    private $priKey = null;
    private $qlKey = null;
    public static $util;

    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }

        return self::$util;
    }

    public function randomkeys($length)
    {
        $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';

        $key    =   '';
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }

    public function __construct(){
        $str = dirname(__DIR__) . '/Key/' . (PRODUCTION_ENV ? '' : '');
        $this->qlKey = file_get_contents($str . 'QL_public_key.pem', 1);
        $this->priKey = file_get_contents($str . 'private_key.pem', 1);
    }

    public function sign($reqData) {
        $my_private_key = openssl_pkey_get_private($this->priKey);
        if (openssl_sign($reqData, $out, $my_private_key)) {
            $sign = base64_encode($out);
        }
        return $sign ? $sign : '';
    }

    private function _encode($data, $code){
        switch (strtolower($code)){
            case 'base64':
                $data = base64_encode(''.$data);
                break;
            case 'hex':
                $data = bin2hex($data);
                break;
            case 'bin':
            default:
        }
        return $data;
    }

    public function public_encrypt($data, $code = 'base64'){
        $encrypted="";
        openssl_public_encrypt($data,$encrypted,$this->qlKey);//公钥加密
        $ret = $this->_encode($encrypted, $code);
        return $ret;
    }
}