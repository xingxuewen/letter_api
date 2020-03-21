<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Constants\UserIdentityConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserIdentity\Alive\DoAliveHandler;
use App\Models\Chain\UserIdentity\FaceAlive\DoFaceAliveHandler;
use App\Models\Chain\UserIdentity\IdcardBack\DoIdcardBackHandler;
use App\Models\Chain\UserIdentity\IdcardFront\DoIdcardFrontHandler;
use App\Models\Chain\UserIdentity\VerifyCarrier\DoVerifyCarrierHandler;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\TianChuang\TianChuangService;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;


/**
 * 用户身份信息认证
 *
 * Class UserAuthenController
 * @package APP\Http\Controllers\V1
 */
class UserIdentityController extends Controller
{

    /**
     * 已实名用户姓名、身份证号
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRealnameAndIdcard(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        $realname = UserIdentityFactory::fetchUserRealInfo($userId);
        //暂无数据
        if (empty($realname)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $data['realname'] = UserIdentityStrategy::formatRealname($realname['name']);
        $data['idcard'] = Utils::formatIdcard($realname['certificate_no']);
        $data['is_realname'] = $realname ? 1 : 0;

        return RestResponseFactory::ok($data);
    }

    /**
     * 运营商实名认证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRealnameInfo(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['mobile'] = $request->user()->mobile;

        //运营商三要素认证责任练
        $res = new DoVerifyCarrierHandler($data);
        $re = $res->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        //身份证号加密
        $params['realname'] = $re['realname'];
        $params['idcard'] = Utils::formatIdcard($re['idcard']);

        return RestResponseFactory::ok($params);
    }

    /**
     * 百款聚到 - 运营商实名认证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateFakeRealnameInfo(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['mobile'] = $request->user()->mobile;
        $data['type'] = $request->input('type','oneloan');

        //虚假实名
        $is_fake_realname = UserIdentityFactory::fetchIsFakeRealname($data);

        if ($is_fake_realname == 1) //
        {
            //数据处理
            $data = UserIdentityStrategy::fetchFakeRealname($data);
            //添加信息
            $re = UserIdentityFactory::updateFakeRealname($data);
        } else //
        {
            //运营商三要素认证责任练
            $res = new DoVerifyCarrierHandler($data);
            $re = $res->handleRequest();
            if (isset($re['error'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
            }
        }

        //身份证号加密
        $params['realname'] = UserIdentityStrategy::formatRealname($re['realname']);
        $params['idcard'] = Utils::formatIdcard($re['idcard']);

        return RestResponseFactory::ok($params);
    }
}