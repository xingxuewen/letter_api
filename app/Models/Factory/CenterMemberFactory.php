<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataUserMemberCenterLog;
use App\Models\Orm\UserOrderType;


class CenterMemberFactory extends AbsModelFactory
{
    /**
     * 会员中心来源流水
     *
     * @param array $params
     * @return bool
     */
    public static function createUserCenterMemberLog($params, $productId, $userId, $userArr, $deliveryId, $deliveryArr)
    {
        $log = new DataUserMemberCenterLog();
        $log->user_id = $userId ? $userId : 0;
        $log->username = $userArr['username'];
        $log->mobile = $userArr['mobile'];
        $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
        $log->click_source = $params['click_source'];
        $log->source_id = $productId;
        $log->product_is_vip = $params['is_vip_product'];
        $log->channel_id = $deliveryId;
        $log->channel_title = $deliveryArr['title'];
        $log->channel_nid = $deliveryArr['nid'];
        $log->shadow_nid = $params['shadow_nid'] ? $params['shadow_nid'] : "sudaizhijia";
        $log->app_name = $params['app_name'] ? $params['app_name'] : "sudaizhijia";
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s');
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

}
