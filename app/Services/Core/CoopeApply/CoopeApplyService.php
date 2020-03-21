<?php

namespace App\Services\Core\CoopeApply;

use App\Models\Factory\OauthFactory;
use App\Services\AppService;
use DB;

/**
 * 合作贷对接产品
 *
 * Class OneloanApplyService
 * @package App\Services\Core\SdPlatform
 */
class CoopeApplyService extends AppService
{

    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 获取url
     *
     * @param $datas
     * @return mixed
     */
    public function toCoopeApplyService($datas)
    {
        $page = $datas['page'];
        //类型id
        //$typeNid = $datas['platform']['type_nid'];
        $typeNid = $datas['product']['type_nid'];
        //判断对接开关
        $id = $datas['product']['id']; //产品id
        $abutSwitch = 1; //1开
        $channelStatus = OauthFactory::checkCoopeChannelStatus($id, $abutSwitch);
        if (!$channelStatus || empty($typeNid)) {
            return $page;
        }
        switch ($typeNid) {
            default:
                break;
        }
        return $page;
    }

}
