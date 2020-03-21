<?php

namespace App\Http\Controllers\V2;

use App\Constants\UserIdentityConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserIdentity\Alive\DoAliveHandler;
use App\Models\Chain\UserIdentity\MegviiAlive\DoMegviiAliveHandler;
use App\Models\Chain\UserIdentity\MegviiBack\DoMegviiCardBackHandler;
use App\Models\Chain\UserIdentity\MegviiFront\DoMegviiCardFrontHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Validator\FaceId\Megvii\MegviiService;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

/**
 * 用户身份信息认证
 *
 * Class UserIdentityController
 * @package App\Http\Controllers\V2
 */
class UserIdentityController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 调取face++获取身份证正面信息
     */
    public function fetchFaceidToCardfrontInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 身份证正面图片以及身份证正面头像图片
        $data['card_front'] = $request->file('cardFront');
        $data['card_photo'] = $request->file('cardPhoto');
        //责任链
        $realname = new DoMegviiCardFrontHandler($data);
        $res = $realname->handleRequest();

        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok($res);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * 调取face++获取身份证反面信息
     */
    public function fetchFaceidToCardbackInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 反面身份证图片
        $data['card_back'] = $request->file('cardBack');
        //验证正面信息是否获取，提示先获取正面信息
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $front = UserIdentityFactory::fetchIdcardinfoById($data);
        if (!$front) {
            //请先完成身份证正面扫描
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(12009), 12009);
        }
        //身份证反面信息责任链
        $tianCheck = new DoMegviiCardBackHandler($data);
        $res = $tianCheck->handleRequest();

        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 活体认证
     * 此接口用于将FaceID MegLiveStill SDK 所获得的数据进行上传，并获取活体验证、人脸比对、攻击防范等结果信息。
     * 注意：本接口仅支持FaceID MegLiveStill SDK 3.0及以上版本的数据
     *
     * @todo 活体检测待完成
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function verifyFaceidToIdcard(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //由FaceID MegLiveStill SDK 3.0及以上版本生成的数据
        $data['meglive_data'] = $request->file('meglive_data', '');

        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_TIAN;
        $realname = UserIdentityFactory::fetchIdcardinfoById($data);
        //该用户以验证过身份信息
        if (!$realname || empty($realname['card_front']) || empty($realname['card_photo'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(11128), 11128);
        }

        //整理数据
        $data = UserIdentityStrategy::getAliveNeedDatas($data, $realname);

        //face++认证活体同步数据记录
        $faceAlive = new DoMegviiAliveHandler($data);
        $faceRes = $faceAlive->handleRequest();
        if (isset($faceRes['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $faceRes['error'], $faceRes['code'], $faceRes['error']);
        }

        return RestResponseFactory::ok($faceRes['info']);
    }

}