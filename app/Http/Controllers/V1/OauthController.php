<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Apply\Apply\DoApplyHandler;
use App\Models\Chain\Apply\CoopeApply\DoCoopeApplyHandler;
use App\Models\Chain\Apply\CreditcardApply\DoCreditcardApplyHandler;
use App\Models\Chain\Apply\RealnameApply\DoRealnameApplyHandler;
use App\Models\Chain\Apply\SpreadApply\DoSpreadApplyHandler;
use App\Models\Chain\Apply\OneloanApply\DoOneloanApplyHandler;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\OauthFactory;
use App\Models\Factory\OneloanProductFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\OauthStrategy;
use App\Strategies\OneloanProductStrategy;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

class OauthController extends Controller
{
        /**
     * @param Request $request
     * @return mixed
     * 产品详情——点击借款
     */
    public function fetchLoanmoney(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;

        //获取平台网址
        $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        if (empty($platformWebsite)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1802), 1802);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);
        //数据处理
        $data = OauthStrategy::getOauthProductDatas($data, $user, $platformWebsite);

        //申请借款责任链
        $re = new DoApplyHandler($data);
        $res = $re->handleRequest();

        //根据配置开关，拼接sign参数,加密请求时间，用于链接有效期校验
        if (!empty($res['apply_url'])) {

            $appkey = platformFactory::fetchPlatformAppkey($data['platformId']);

            $res['apply_url'] = Utils::addSignToUrl($appkey,$res['apply_url']);
        }

        return RestResponseFactory::ok(['url'=>$res['apply_url']]);
    }


    /**
     * 需要登录的立即申请
     * 根据产品id,获取对应的甲方地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchApplyUrlBySwitch(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        //与上下线没有关系 开启
        $data['is_nothing'] = $request->input('webSwitch', 0);
        $data['type'] = $request->input('type', 4);
        $data['cacheSign'] = $request->input('cacheSign', 0);
        $mobile = $request->user()->mobile;

        //添加日志，存储前端数据
        logInfo('v1_oauth_fetchApplyUrlBySwitch_' . $mobile, ['data' => $data]);

        //获取平台网址
        if (isset($data['is_nothing']) && $data['is_nothing'] == 1) //开关开启 与上下线无关
        {
            $platformWebsite = PlatformFactory::fetchProductWebsiteNothing($data['productId']);
        } else //与上下线有关
        {
            $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        }

        //申请人数已满，请改天再来~
        if (empty($platformWebsite)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1802), 1802);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($data['userId']);
        //数据处理
        $data = OauthStrategy::getOauthProductDatas($data, $user, $platformWebsite);

        //申请借款责任链
        $re = new DoRealnameApplyHandler($data);
        $res = $re->handleRequest();

        //根据配置开关，拼接sign参数,加密请求时间，用于链接有效期校验
        if (!empty($res['url'])) {

            $appkey = platformFactory::fetchPlatformAppkey($data['platformId']);

            $res['url'] = Utils::addSignToUrl($appkey,$res['url']);
        }

        //点击的产品id存redis
        if ($data['cacheSign'] == 1) CacheFactory::putProductIdToCache($data);

        return RestResponseFactory::ok($res);
    }


    /**
     * 立即申请撞库判断
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductIsAuthenEtc(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //与上下线没有关系 开启
        $data['is_nothing'] = $request->input('webSwitch', 1);

        //产品是否需要认证信息
        //获取平台网址
        if (isset($data['is_nothing']) && $data['is_nothing'] == 1) //开关开启 与上下线无关
        {
            $platformWebsite = PlatformFactory::fetchProductWebsiteNothing($data['productId']);
        } else //与上下线有关
        {
            $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        }

        $res['is_authen'] = 0;
        if (isset($platformWebsite['is_authen']) && $platformWebsite['is_authen'] == 1) //
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
        if ($data['userId']) //用户存在查询信息
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
        $data['user'] = UserIdentityStrategy::getSpreadUserInfo($user, $data);

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


    /**
     * 置顶信用卡 - 对接
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardUrl(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['configId'] = $request->input('configId', '');

        //信用卡配置详情
        $data['config'] = CreditcardFactory::fetchCreditcardConfigInfoById($data['configId']);
        if (empty($data['config'])) //无显示数据
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $user = [];
        if ($data['userId']) //用户存在进行查询
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
        $data['user'] = UserIdentityStrategy::getSpreadUserInfo($user, $data);

        if (empty($data['config'])) //无显示数据
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $res = new DoCreditcardApplyHandler($data);
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
        $spreads['creditcard'] = isset($re['creditcard']) ? $re['creditcard'] : RestUtils::getStdObj();

        return RestResponseFactory::ok($spreads);
    }

    /**
     * 一键贷产品——立即申请
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOneloanApply(Request $request)
    {
        $data = $request->all();

        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;
        $data['type'] = $request->input('type', 4);

        //获取平台网址
        $website = OneloanProductFactory::fetchWebsiteUrl($data['id']);
        if (empty($website)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);
        //数据处理
        $data = OneloanProductStrategy::getOauthProductDatas($data, $user, $website);

        //申请借款责任链
        $re = new DoOneloanApplyHandler($data);
        $urlArr = $re->handleRequest();

        $res['url'] = $urlArr;
        return RestResponseFactory::ok($res);
    }

    /**
     * 合作贷产品 - 立即申请
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCooperateUrl(Request $request)
    {
        $data = $request->all();

        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        $data['type'] = $request->input('type', 4);
        $data['productId'] = $request->input('productId', '');
        $data['typeId'] = $request->input('typeId', 0);

        //获取平台网址
        $website = OauthFactory::fetchCooperateWebsiteUrl($data);
        if (empty($website)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);
        //数据处理
        $data = OneloanProductStrategy::getOauthProductDatas($data, $user, $website);

        //申请借款责任链
        $re = new DoCoopeApplyHandler($data);
        $urlArr = $re->handleRequest();

        $res['url'] = $urlArr;
        return RestResponseFactory::ok($res);
    }
}