<?php

namespace App\Http\Controllers\V1;


use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Factory\UserFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\CenterMemberFactory;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\MemberPurchaseFactory;

class MemberPurchaseController extends Controller
{
    /**会员购买类型记录流水
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPurchaseType(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $sub_type = $request->input('subtype');
        $data['deviceId'] = $request->input('deviceId', '');
        $data['shadow_nid'] = $request->input('shadow_nid', '');
        $data['app_name'] = $request->input('app_name', '');
        $subtype = array_filter(explode(',', $sub_type));
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($data['userId']);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
        if (empty($userArr) || empty($deliveryArr) || empty($deliveryId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //添加会员购买类型流水
        MemberPurchaseFactory::createUserVipTypeLog($data, $subtype, $userArr, $deliveryId, $deliveryArr);

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**会员中心来源统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMemberCenter(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $productId = $request->input('productId');
        $data['deviceId'] = $request->input('deviceId', '');
        $data['shadow_nid'] = $request->input('shadow_nid', '');
        $data['app_name'] = $request->input('app_name', '');
        $data['click_source'] = $request->input('click_source', '');
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //判断是否是vip产品
        $data['productId'] = $productId;
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);
        if (empty($userArr) || empty($productArr) || empty($deliveryArr) || empty($deliveryId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //添加会员中心来源
        CenterMemberFactory::createUserCenterMemberLog($data, $productId, $userId, $userArr, $deliveryId, $deliveryArr);

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }
}