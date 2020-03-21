<?php
namespace App\Services\Core\Oneloan\Jiajiarong\Config;

class JiajiarongConfig {
    // url
    //正式环境
    const FORMAL_URL = 'http://crm.jjrjd.com/api/reg';
    //测试环境
    const TEST_URL = 'http://crm.jjrjd.com/api/test';
    //对应真实环境
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    // 渠道id
    const CID =  PRODUCTION_ENV ? 176 : 176;
    //媒体代码
    const SRC = 'zj01';
    const KEY='aa81ec4afc277b97';

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
    /**
     * @param string $house
     * @return string $house
     */
    /**
     * 房产信息, 000无房, 001有房贷, 002无房贷
     *          1全款房，2 月供房， 3 无
     * @param string $param
     * @return int
     */
    public static function getHouse($param = '')
    {
        if ('000' == $param) return 3;
        elseif ('001' == $param) return 2;
        elseif ('002' == $param) return 1;
        else return 3;
    }
    /**
     * 房产信息, 000无车, 001有车贷, 002无车贷
     *          1全款车，2 月供车， 3 无
     * @param string $param
     * @return int
     */
    public static function getCar($param = '')
    {
        if ('000' == $param) return 3;
        elseif ('001' == $param) return 2;
        elseif ('002' == $param) return 1;
        else return 3;
    }
    /**
     * 公积金
     * @param 000 无公积金, 001 1年以内, 002 1年以上
     *        1:无，2：一年内，3：超一年
     * @return int
     */
    public static function getAccFund($param = '')
    {
        if ('000' == $param) return 1;
        elseif ('001' == $param) return 2;
        elseif ('002' == $param) return 3;
        else return 3;
    }

}
