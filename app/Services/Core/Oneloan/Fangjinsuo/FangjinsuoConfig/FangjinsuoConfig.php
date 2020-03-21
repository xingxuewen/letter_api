<?php

namespace App\Services\Core\Oneloan\Fangjinsuo\FangjinsuoConfig;

/**
 *  房金所配置
 */
class FangjinsuoConfig
{
    //测试环境
    const TEST_URL = 'http://beta-ocdc.fang-crm.com/api/index/add?key=G2iJdcjjhyKAv21pS9KPCwrmmwenshngBDvXF6It';
    //正式环境
    const FORMAL_URL = 'http://ocdc.fang-crm.com/api/index/add?key=vE8XBwGNmd10UR1J5LuNQs1Tdh7gYMmrElILDEzS';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    /**
     * @param string $city
     * @return string $city
     */
    static public function getCity($city = ''){
        if (strstr($city, '市')) {
            $city = strstr($city, '市', true);
        }
        return $city;
    }
}
