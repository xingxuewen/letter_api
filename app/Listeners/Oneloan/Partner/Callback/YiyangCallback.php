<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Yiyang\YiyangConfig;
use App\Strategies\SpreadStrategy;
use Illuminate\Support\Facades\Log;
use App\Helpers\Logger\SLogger;
class YiyangCallback
{
    /**
     * 意扬回调处理
     * @param array $res
     * @param array $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {//Log::info('结果', ['data'=>$res]);
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['status']) and $res['status'] == 200) {
            if (isset($res['data']['code']) and $res['data']['code']==1 || $res['data']['code']==3) {
                $spread['status'] = 1;
                $spread['group_status'] = 1;
                $spread['response_code'] = 1;
                $spread['message']=isset($res['data']['tips'])?$res['data']['tips']:'成功';
            } else {
                $spread['status'] = 0;
                $spread['group_status'] = 0;
                $spread['response_code'] = 2;
                $spread['message']=isset($res['data']['tips'])?$res['data']['tips']:'失败';
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
