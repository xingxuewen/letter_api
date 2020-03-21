<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Models\Factory\UserSpreadFactory;
use App\Strategies\SpreadStrategy;
use App\Services\Core\Oneloan\Kuailaiqian\Zhanyewang\ZhanyewangService;
use App\Helpers\Logger\SLogger;
/**
 *  展业王回调处理
 */
class ZhanyewangCallback
{

    /**
     * 展业王回调处理
     * @param array $res
     * @param array $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
//        dd(111);
        logError(json_encode($res,JSON_UNESCAPED_UNICODE));
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['resultCode']) && $res['resultCode'] == 0) {
            $spread['status'] = 1;
            $spread['group_status'] = 1;
            $spread['message'] = isset($res['Msg']) ? $res['Msg'] : '成功';
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['group_status'] = 0;
            $spread['message'] = isset($res['Msg']) ? $res['Msg'] : '失败';
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

        if($spread['status']==1){
            //将明文手机号和姓名发送给对方
            ZhanyewangService::pushdata($spread,
                function($res) use ($spread) {
                    logInfo('展业王', ['spread' => $spread, 'res' => $res]);
                }, function ($e) {

                });
        }
        return $spread;
    }
}