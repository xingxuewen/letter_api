<?php

namespace App\Services\Core\Platform\Quhuafenqi\Util;

class Crypt3Des{
    private $iv="aabbccdd";
    private $key =null;
    public static $util;

    function setCrypt3Des($key){
        $this->key=$key;
    }

    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }
        return self::$util;
    }

    function encrypt($input){
        $size = mcrypt_get_block_size(MCRYPT_3DES,MCRYPT_MODE_ECB);
        $input = $this->pkcs5_pad($input, $size);
        $key = str_pad($this->key,24,'0');
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
        @mcrypt_generic_init($td, $key, $this->iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }

    function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
}