<?php

namespace App\Helpers;

class MCrypt
{
    private $iv = "1234567890123456"; // 密钥偏移量IV，可自定义
    private $encryptKey = "1234567890123456"; // AESkey，可自定义

    public function __construct($encryptKey = '', $iv = '')
    {
        if (!empty($encryptKey)) {
            $this->encryptKey = $encryptKey;
        }

        if (!empty($iv)) {
            $this->iv = $iv;
        }
    }

    //加密
    public function encrypt($encryptStr)
    {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;

        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);

        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples
        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);

        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        return bin2hex($encrypted);

    }

    //解密
    public function decrypt($encryptStr)
    {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;

        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
        mcrypt_generic_init($module, $encryptKey, $localIV);

        $encryptedData = $this->hex2bin($encryptStr);
        $encryptedData = mdecrypt_generic($module, $encryptedData);

        return $encryptedData;
    }

    public function hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }
}