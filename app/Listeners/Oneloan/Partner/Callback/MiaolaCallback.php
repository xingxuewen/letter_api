<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Models\Factory\UserSpreadFactory;
use App\Strategies\SpreadStrategy;
use App\Models\Cache\CommonCache;
use App\Events\Oneloan\Partner\UserMiaolaEvent;


/**
 *  秒拉回调处理
 */
class MiaolaCallback
{

    /**
     * 秒拉回调处理
     * @param $res
     * @param $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //我们好像所有的都是OPEN55020这个错误码，我就用这个了，到时候再次请求一次，弥补一下验签错误的情况
        if (isset($res['success']) && $res['success'] == 0 && $res['errCode'] == 'OPEN55020') {
            //删除cache缓存，重新获取
            CommonCache::delCache(CommonCache::MIAOLA_TOKEN);
            //重新请求接口
            event(new UserMiaolaEvent($spread));
        }

        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['success']) && $res['success'] == 1) {
            $spread['status'] = 1;
            $spread['group_status'] = 1;
            $spread['message'] = isset($res['data']) ? $res['data'] : '成功';
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['group_status'] = 0;
            $spread['message'] = isset($res['errMsg']) ? $res['errMsg'] : '失败';
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
