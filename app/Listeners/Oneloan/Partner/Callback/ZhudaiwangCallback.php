<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Zhudaiwang\Config\ZhudaiwangConfig;

/**
 *  助贷网回调处理
 */
class ZhudaiwangCallback
{

    /**
     * 助贷网回调处理
     * @param $res
     * @param $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //处理结果
        $spread['result'] = $res;
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = ZhudaiwangConfig::getMessage(intval($res));
        $spread['response_code'] = 0;

        if (intval($res) > 1000000) {
            $spread['status'] = 1;
            $spread['group_status'] = 1;
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['group_status'] = 0;
            $spread['response_code'] = 2;
        }

        // 更新spreadLog
        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        }

        // 更新分组分发流水表
        if (isset($spread['group_id']) && !empty($spread['group_id'])) {
            UserSpreadFactory::insertOrUpdateUserSpreadGroupLog($spread);
        }

        // 更新推送次数等数据
        SpreadStrategy::updateSpreadCounts($spread);

        return $spread;
    }

}