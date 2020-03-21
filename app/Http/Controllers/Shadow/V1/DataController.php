<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\DataFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * Class DataController
 * @package App\Http\Controllers\V1
 * 数据统计
 */
class DataController extends Controller
{

    /**
     * 区域点击统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserRegionLog(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['deviceId'] = $request->input('deviceId', '');
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');

        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //区域点击流水统计
        $log = BannersFactory::createUserRegionLog($data, $deliverys);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}