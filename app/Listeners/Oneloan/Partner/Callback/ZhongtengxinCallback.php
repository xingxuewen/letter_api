<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;

/**
 *  中腾信回调处理
 */
class ZhongtengxinCallback
{
    /**
     * 中腾信回调处理
     * @param $res
     * @param $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['result']) && $res['result'] == 'success') {
            $spread['status'] = 1;
            $spread['group_status'] = 1;
            $spread['message'] = '成功';
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['group_status'] = 0;
            $spread['message'] = isset($res['result']) ? $res['result'] : '失败';
            $spread['response_code'] = 2;
        }

        // 更新spreadLog
        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
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