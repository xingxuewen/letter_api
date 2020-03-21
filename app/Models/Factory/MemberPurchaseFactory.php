<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataUserVipSubTypeLog;
use App\Models\Orm\UserOrderType;


class MemberPurchaseFactory extends AbsModelFactory
{
    /**
     * 会员购买类型统计
     *
     * @param array $params
     * @return bool
     */
    public static function createUserVipTypeLog($params, $subtype, $userArr, $deliveryId, $deliveryArr)
    {
        $date = date('Y-m-d H:i:s', time());
        foreach ($subtype as $val) {
            $log = new DataUserVipSubTypeLog();
            $log->user_id = isset($params['userId']) ? $params['userId'] : 0;
            $log->username = $userArr['username'];
            $log->mobile = $userArr['mobile'];
            $log->channel_id = $deliveryId;
            $log->channel_title = $deliveryArr['title'];
            $log->channel_nid = $deliveryArr['nid'];
            $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
            $log->type_id = $val;
            $log->user_agent = UserAgent::i()->getUserAgent();
            $log->created_at = $date;
            $log->shadow_nid = $params['shadow_nid'] ? $params['shadow_nid'] : "sudaizhijia";
            $log->app_name = $params['app_name'] ? $params['app_name'] : "sudaizhijia";
            $log->created_ip = Utils::ipAddress();

            $log->save();
        }

    }

}
