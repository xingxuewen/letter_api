<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Cache\OxygenDaiCache;


/**
 *  氧气贷回调处理
 */
class OxygendaiCallback
{
    /*
     * 氧气贷回调处理
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

        if (isset($res['ret'])) {
            if ($res['ret'] == '0') {
                if (isset($res['data']['isSuccess'])) {
                    if ($res['data']['isSuccess'] == 'T') {
                        $spread['message'] = '成功';
                        $spread['status'] = 1;
                        $spread['group_status'] = 1;
                        $spread['response_code'] = 1;
                    } else {
                        $spread['message'] = $res['data']['errMsg'];
                        $spread['group_status'] = 0;
                        $spread['response_code'] = 2;
                    }

                }

            } else {
                if ($res['ret'] == '13002' || $res['ret'] == '13012') {
                    //删除tokenid
                    OxygenDaiCache::delCache(OxygenDaiCache::TOKENID);
                }
                $spread['message'] = $res['msg'];
                $spread['response_code'] = 2;
            }
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