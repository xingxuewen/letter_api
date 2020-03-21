<?php

namespace App\Services\Core\Oneloan\Yiyang\YiyangConfig;


/**
 * 意扬配置
 */
class YiyangConfig
{

    const SITEID='1350_1001';
    const CSCODE='gi';
    const SECRET='pcldmzynykuy';

    const URL='https://www.yyang.net.cn/api/v1.0/ins';

    /**
     * 获取工资
     * @param string $salary
     * 收入，10000以内/1-2万/2-5万/5万以上
     * 101:2千以下，102:2千-3千，103:3千-4千，104:4千-5千，105:5千-1万，106:1万以上
     * @return int
     */
    public static function getIncome($salary = '')
    {
        $res='10000以内';
        if ($salary == '101' || $salary == '102' || $salary == '103' || $salary == '104' || $salary == '105'){
            $res= '10000以内';
        }elseif($salary == '106')  {
            $res= '1-2万';
        }
        return $res;
    }

}