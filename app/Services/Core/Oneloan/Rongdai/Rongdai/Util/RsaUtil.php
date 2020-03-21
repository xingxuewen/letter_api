<?php

namespace App\Services\Core\Oneloan\Rongdai\Rongdai\Util;

use App\Services\Core\Oneloan\Rongdai\Rongdai\Config\Config;

/**
 * 加密处理
 *
 * Class RsaUtil
 * @package App\Services\Core\Platform\Rongdai\Rongdai\Util
 */
class RsaUtil
{
    private static $key = Config::ENCRYPT_SECRET;

    public static function setKey($key)
    {
        self::$key = $key;
    }

    /**
     * 加密
     *
     * @param $input
     * @return string
     */
    public static function encrypt($input = '')
    {
        $res = openssl_encrypt($input, 'aes-256-ecb', self::$key);

        return $res;
    }

    public static function decrypt($input)
    {
        $input = base64_decode($input);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::$key, $input, MCRYPT_MODE_ECB);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s - 1]);
        return substr($decrypted, 0, -$padding);
    }


    /**
     * 格式化月收入
     * //月收入范围, 001:2000以下，002:2000-5000,003:5000-1万，004：1万以上；
     * //月收入范围,;101:2千以下，102:2千-3千，103:3千-4千，104:4千-5千，105:5千-1万，106:1万以上
     *
     * @param string $salary
     * @return int|mixed
     */
    public static function formatSalary($salary = '')
    {
        $data = [
            '001' => 2000,
            '002' => 5000,
            '003' => 10000,
            '004' => 10000,
            '101' => 2000,
            '102' => 3000,
            '103' => 4000,
            '104' => 5000,
            '105' => 10000,
            '106' => 10000,
        ];

        return $data[$salary] ? $data[$salary] : 2000;
    }

    /**
     * 工资发放形式
     * //001银行转账, 002现金发放',
     * //工资发放形式 1银行代发 2银行转账 3现金发放 4自由职业收入
     *
     * @param string $salaryExtend
     * @return int|mixed
     */
    public static function formatSalaryExtend($salaryExtend = '')
    {
        $data = [
            '001' => 1,
            '002' => 3,
        ];

        return $data[$salaryExtend] ? $data[$salaryExtend] : 1;
    }
}