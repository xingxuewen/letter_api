<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Apply\SpreadApply\DoSpreadApplyHandler;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

class OauthController extends Controller
{
    /**
     * 立即申请撞库判断
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductIsAuthenEtc(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //产品是否需要认证信息
        //获取平台网址
        $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        $res['is_authen'] = 0;
        if ($platformWebsite['product_channel_status'] == 1 && $platformWebsite['is_authen'] == 1) //
        {
            $res['is_authen'] = 1;
        }

        //用户是否已实名认证
        $realname = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        $res['is_realname'] = empty($realname) ? 0 : 1;

        return RestResponseFactory::ok($res);
    }

    /**
     * 一键选贷款 - 对接
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpreadUrl(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['configId'] = $request->input('configId', '');

        //一键选贷款配置详情
        $data['config'] = UserSpreadFactory::fetchSpreadConfigInfoById($data['configId']);
        if (empty($data['config'])) //无显示数据
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $user = [];
        if($data['userId']) //用户存在进行查询
        {
            //用户定位信息
            $user['location'] = DeviceFactory::fetchDevicesByUserId($data['userId']);
            //用户信息
            $user['user_info'] = UserFactory::fetchUserByMobile($data['mobile']);
            //用户实名信息
            $user['realname_info'] = UserIdentityFactory::fetchUserRealInfo($data['userId']);
            //用户虚假实名信息
            $user['fake_info'] = UserIdentityFactory::fetchFakeUserRealInfo($data['userId'], $data['config']['type_nid']);
        }

        //处理用户信息
        $data['user'] = UserIdentityStrategy::getSpreadUserInfo($user,$data);

        if (empty($data['config'])) //无显示数据
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $res = new DoSpreadApplyHandler($data);
        $re = $res->handleRequest();

        if (isset($re['error']) && $re['error'] && $re['code'] == 401) //登录
        {
            return RestResponseFactory::unauthorized();
        }

        if (isset($re['error'])) //错误提示
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        $spreads['url'] = $re['url'];
        $spreads['spread'] = isset($re['spread']) ? $re['spread'] : RestUtils::getStdObj();

        return RestResponseFactory::ok($spreads);
    }

}