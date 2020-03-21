<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserConstant;
use App\Events\V1\UserUnlockLoginEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\UserFactory;
use Illuminate\Http\Request;

/**
 * 用户联登
 *
 * Class UserUnlockLoginController
 * @package App\Http\Controllers\V1
 */
class UserUnlockLoginController extends Controller
{
    /**
     * 用户登录记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserUnlockLogin(Request $request)
    {
        //接收参数
        $data = $request->all();
        $data['nowDate'] = date('Y-m-d');
        $data['userId'] = $request->user()->sd_user_id;

        /*
        //判断是新老用户  新用户不进行统计
        $checkNew = UserFactory::fetchUserIsNew($data['userId']);
        if ($checkNew) {
            return RestResponseFactory::ok(RestUtils::getStdObj());
        }
        */

        //用户基本信息
        $data['user'] = UserFactory::fetchUserNameAndMobile($data['userId']);

        //用户渠道信息
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($data['userId']);
        $data['deliverys'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //添加用户联登流水
        $log = UserFactory::createUserUnlockLoginLog($data);
        if (!$log) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        /*
        //用户连续登录天数
        $continueCounts = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);

        //当联登天数大于3时，不在进行计算
        if (!$continueCounts || $continueCounts['login_count'] < UserConstant::USER_CONTINUE_LOGIN_DAYS) {
            event(new UserUnlockLoginEvent($data));
        }
        */

        event(new UserUnlockLoginEvent($data));

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}