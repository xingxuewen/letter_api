<?php

namespace App\Http\Controllers\V2;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Apply\DoApplyHandler;
use App\Models\Chain\Apply\RealnameApply\DoRealnameApplyHandler;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\OauthStrategy;
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
        $mobile = $request->user()->mobile;
        $data['userId'] = $userId;
        $data['cacheSign'] = $request->input('cacheSign', 0);


        //限量产品提示语
        $info = ProductFactory::fetchProductInfoByProId($data['productId']);
        if ($info && $info['is_delete'] == 2) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1803), 1803);
        }
        //添加日志，存储前端数据
        logInfo('v2_oauth_fetchLoanmoney_' . $mobile, ['data' => $data]);

        //获取平台网址
        $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        //产品下线提示
        if (empty($platformWebsite)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1802), 1802);
        }
        //暂无产品数据
        if (empty($platformWebsite)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);
        //数据处理
        $data = OauthStrategy::getOauthProductDatas($data, $user, $platformWebsite);

        //申请借款责任链
        $re = new DoRealnameApplyHandler($data);
        $res = $re->handleRequest();

        logInfo('apply-redis',['data'=>$data]);
        //点击的产品id存redis
        if ($data['cacheSign'] == 1) CacheFactory::putProductIdToCache($data);

        //根据配置开关，拼接sign参数,加密请求时间，用于链接有效期校验
        if (!empty($res['url'])) {

            $appkey = platformFactory::fetchPlatformAppkey($data['platformId']);

            $res['url'] = Utils::addSignToUrl($appkey,$res['url']);
        }

        return RestResponseFactory::ok($res);
    }


}