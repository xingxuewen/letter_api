<?php
namespace App\Services\Core\Oneloan\Chunyu\Config;

/**
 *  春雨配置
 */
class ChunyuConfig
{

    //对应真实环境
    const REAL_URL = 'http://api.datasuv.net/data/add?m=Ml8xXzM5MV8yOTQ=';

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
