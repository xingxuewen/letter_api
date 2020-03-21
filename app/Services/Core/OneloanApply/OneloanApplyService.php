<?php

namespace App\Services\Core\OneloanApply;

use App\Models\Factory\OauthFactory;
use App\Services\AppService;
use DB;

/**
 * 一键贷对接产品
 *
 * Class OneloanApplyService
 * @package App\Services\Core\SdPlatform
 */
class OneloanApplyService extends AppService
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
     * @param $params
     * @return mixed
     * 获取对接url
     */
    public function toOneloanApplyService($datas)
    {
        $page = $datas['page'];
        //类型id
        //$typeNid = $datas['platform']['type_nid'];
        $typeNid = $datas['product']['type_nid'];
        //判断对接开关
        $id = $datas['id']; //产品id
        $abutSwitch = 1; //1开
        $channelStatus = OauthFactory::checkOneloanChannelStatus($id, $abutSwitch);
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
