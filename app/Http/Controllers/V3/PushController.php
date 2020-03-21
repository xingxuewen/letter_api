<?php

namespace App\Http\Controllers\V3;

use App\Constants\PopConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\PushFactory;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;

/**
 * 推送
 *
 * Class PushController
 * @package App\Http\Controllers\V3
 */
class PushController extends Controller
{
    /**
     * 批量弹窗
     * 0  首页  1  我的  2  积分
     * 添加时间段限制
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPopUp(Request $request)
    {
        $data = $request->all();
        $data['deviceId'] = $request->input('deviceId', '');
        $data['position'] = $request->input('position');
        //查询需要推送的信息
        $data['versionCode'] = PopConstant::PUSH_VERSION_CODE_ONELOAN_WECHAT;
        //查询用户范围
        $device = DeviceFactory::fetchIsNewUserByDeviceId($data);

        if ($device) {
            $start = date('Y-m-d 00:00:00');
            $end = date('Y-m-d 23:59:59');
            //是否是当天注册
            if ($device['updated_at'] >= $start && $device['updated_at'] <= $end) {
                $data['is_new'] = 1;
            } else {
                $data['is_new'] = 2;
            }

        } else {
            $data['is_new'] = 1;
        }

        //时间范围内数据
        $push = PushFactory::fetchPopupsLimitDate($data);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }

        $pushArr = PushStrategy::getPopups($push);

        return RestResponseFactory::ok($pushArr);
    }
}