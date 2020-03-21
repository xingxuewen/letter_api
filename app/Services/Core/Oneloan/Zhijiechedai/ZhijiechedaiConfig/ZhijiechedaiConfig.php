<?php

namespace App\Services\Core\Oneloan\Zhijiechedai\ZhijiechedaiConfig;

class ZhijiechedaiConfig
{
    //正式环境
    const FORMAL_URL = 'https://gd-server.finupgroup.com/';
    //测试环境
    const TEST_URL = 'http://testgd-server.finupgroup.com/';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //渠道码
    const CHANNEL_CODE = 'CHANNEL_ZJ';
    //client_id
    const CLIENT_ID = 'CHANNEL_ZHIJIE';

    /**
     * 月收入范围,001:2000以下，002:2000-5000,003:5000-1万，004：1万以上
     * 101:2千以下，102:2千-3千，103:3千-4千，104:4千-5千，105:5千-1万，106:1万以上'
     * @param array $params
     * @return int
     */
    public static function formatSalary($params = [])
    {
        switch ($params['salary']) {
            case '001':
                $salaryVal = 2000;
                break;
            case '002':
                $salaryVal = bcdiv(bcadd(2000, 5000), 2);
                break;
            case '003':
                $salaryVal = bcdiv(bcadd(5000, 10000), 2);
                break;
            case '004':
                $salaryVal = 10000;
                break;
            case '101':
                $salaryVal = 2000;
                break;
            case '102':
                $salaryVal = bcdiv(bcadd(2000, 3000), 2);
                break;
            case '103':
                $salaryVal = bcdiv(bcadd(3000, 4000), 2);
                break;
            case '104':
                $salaryVal = bcdiv(bcadd(4000, 5000), 2);
                break;
            case '105':
                $salaryVal = bcdiv(bcadd(5000, 10000), 2);
                break;
            case '106':
                $salaryVal = 10000;
                break;
            default:
                $salaryVal = 0;
        }
        return intval($salaryVal);
    }
}