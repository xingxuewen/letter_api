<?php

namespace App\Services\Core\Spread\Rong360\Util;

use App\Services\Core\Spread\Rong360\Config\Config;

/**
 * 数据处理
 *
 * Class RsaUtil
 * @package App\Services\Core\Spread\Rong360\Util
 */
class RsaUtil
{
    /**
     * 融360获取加密的token
     *
     * @param array $user
     * @return string
     */
    public static function fetchToken($datas = [])
    {
        //字符串拼接
        $string = '';
        foreach ($datas as $key => $val) {
            $string .= $val;
        }

        return md5($string);
    }
}
