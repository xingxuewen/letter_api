<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;

/**
 *  新一贷回调处理
 */
class XinyidaiCallback
{
    /**
     * 新一贷回调处理
     * @param $res
     * @param $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['response_code'] = 0;
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';

        if (isset($res['responseCode'])) {
            if ($res['responseCode'] == '000000') {
                $spread['message'] = isset($res['responseMsg']) ? $res['responseMsg'] : '成功';
                $spread['status'] = 1;
                $spread['group_status'] = 1;
                $spread['response_code'] = 1;
            } else {
                $spread['message'] = isset($res['responseMsg']) ? $res['responseMsg'] : '失败';
                $spread['status'] = 0;
                $spread['group_status'] = 0;
                $spread['response_code'] = 2;
            }
        }

        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            // 更新分发数据状态
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