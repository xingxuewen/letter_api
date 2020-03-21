<?php

namespace App\Services\Core\Oneloan\Jibaodai\Jibaodai\Util;

use App\Services\Core\Oneloan\Jibaodai\Jibaodai\Config\Config;

/*
 * 工厂数据
 *
 * Class RsaUtil
 * @package App\Services\Core\Platform\Jibaodai\Jibaodai
 */
class RsaUtil
{

    /**
     * 生成验签
     * 验证戳(指定字符 MD5 加密后字符，原字符 (channelNo+name+mobile+密钥+applyTime)
     *
     * @param array $params
     * @return string
     */
    public static function generateSign($params = [])
    {
        $sign = Config::CHANNEL_NO . $params['name'] . $params['mobile'] . Config::SECRET_KEY . $params['applyTime'];

        return md5($sign);
    }


    /**
     * 格式化生日
     * 1985-09-11 00:00:00 => 1985-09-11
     *
     * @param string $birth
     * @return string
     */
    public static function formatBirthday($birth = '')
    {
        if (empty($birth)) return '';
        $birth = explode(' ', $birth);
        return $birth ? $birth[0] : '';
    }

    /**
     * 公积金格式转化
     * //0 表示无公积金，1 一年以内， 2 一年以上
     * //000 无公积金, 001 1年以内, 002 1年以上
     *
     * @param string $found
     * @return int
     */
    public static function formatAccumulationFund($found = '')
    {
        if ($found == '000') $res = 0;
        elseif ($found == '001') $res = 1;
        elseif ($found == '002') $res = 2;
        else $res = 0;

        return $res;
    }

    /**
     * 格式化城市名称
     * 北京市 => 北京
     * 去掉市
     *
     * @param string $city
     * @return mixed|string
     */
    public static function formatCity($city = '')
    {
        $city = str_replace('市', '', $city);

        return $city ? $city : '';
    }
}