<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Generator\TokenGenerator;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Token;
use App\Http\Controllers\Controller;
use App\Models\Chain\Sms\Register\DoSmsRegisterHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\SmsFactory;
use App\Models\Factory\UserFactory;
use App\Services\Core\Sms\SmsService;
use App\Strategies\SmsStrategy;
use Illuminate\Http\Request;
use DB;
use Log;

class SmsController extends Controller
{

    /**
     * 注册---短信验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        #获取传过来的手机号
        $data = $request->all();
        $data['smsSign'] = $request->input('smsSign', 'sudaizhijia');
        //渠道
        $data['channel_fr'] = $request->input('channel_fr', 'channel_2');
        //渠道信息
        $deliverys = DeliveryFactory::getDeliveryByNid($data['channel_fr']);
        $data['channel_id'] = $deliverys ? $deliverys['id'] : '';
        $data['channel_title'] = $deliverys ? $deliverys['title'] : '';
        $data['channel_nid'] = $deliverys ? $deliverys['nid'] : '';

        //验证短信1分钟之内不能重复发送
        $not_exprise = SmsFactory::checkCodeExistenceTime($data['mobile'], 'register');
        $expired = SmsFactory::checkCodeExistenceTime($data['mobile'], 'register_expire');
        if (!$not_exprise || !$expired) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1201), 1201);
        }

        $user = UserFactory::fetchUserByMobile($data['mobile']);
        $isNewUser = empty($user) ? 1 : 0;

        //短信注册链
        $smsRegister = new DoSmsRegisterHandler($data);
        $re = $smsRegister->handleRequest();

//        if (!$re)
//        {
//            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1205), 1205);
//        }
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        $re['is_new_user'] = $isNewUser;

        return RestResponseFactory::ok($re);
    }

    /**
     * 修改密码 —— 短信验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function password(Request $request)
    {
        $data = $request->all();
        $data['mobile'] = $request->input('mobile');
        $data['smsSign'] = $request->input('smsSign', 'sudaizhijia');
        //渠道
        $data['channel_fr'] = $request->input('channel_fr', 'channel_2');
        //渠道信息
        $deliverys = DeliveryFactory::getDeliveryByNid($data['channel_fr']);
        $data['channel_id'] = $deliverys ? $deliverys['id'] : '';
        $data['channel_title'] = $deliverys ? $deliverys['title'] : '';
        $data['channel_nid'] = $deliverys ? $deliverys['nid'] : '';

        //验证短信1分钟之内不能重复发送
        $not_exprise = SmsFactory::checkCodeExistenceTime($data['mobile'], 'password');
        if (!$not_exprise) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1201), 1201);
        }

        $code = mt_rand(1000, 9999);
        //$data['message'] = "验证码：{$code}，请勿泄露，关注官方微信“速贷之家官微”，畅享在线客服，第一时间获取最新借款动态！";
        if (isset($data['smsSign']) && $data['smsSign'] != 'sudaizhijia') //非速贷之家短信
        {
            $data['message'] = "验证码：{$code}，此验证码十分钟后失效，请勿泄露给他人";

        } else //速贷之家短信
        {
            $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        }

        //签名
        $data['sign'] = SmsStrategy::getSmsSignByAppname($data);
        $data['code'] = $code;
        $re = SmsService::i()->to($data);
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();
        SmsFactory::putSmsCodeToCache('password_code_' . $data['mobile'], $code);
        SmsFactory::putSmsCodeToCache('password_random_' . $data['mobile'], $random);
        if (!$re) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1205), 1205);
        }
        return RestResponseFactory::ok($random);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     * 忘记密码
     */
    public function forgetPwd(Request $request)
    {
        $data = $request->all();
        $data['mobile'] = $request->input('mobile');
        $data['smsSign'] = $request->input('smsSign', 'sudaizhijia');
        //渠道
        $data['channel_fr'] = $request->input('channel_fr', 'channel_2');
        //渠道信息
        $deliverys = DeliveryFactory::getDeliveryByNid($data['channel_fr']);
        $data['channel_id'] = $deliverys ? $deliverys['id'] : '';
        $data['channel_title'] = $deliverys ? $deliverys['title'] : '';
        $data['channel_nid'] = $deliverys ? $deliverys['nid'] : '';

        //验证短信1分钟之内不能重复发送
        $not_exprise = SmsFactory::checkCodeExistenceTime($data['mobile'], 'forgetpwd');
        if (!$not_exprise) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1201), 1201);
        }

        //验证用户是否存在
        $userinfo = UserFactory::getIdByMobile($data['mobile']);
        if (empty($userinfo)) {
            // 用户名不存在
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1106), 1106);
        }

        // 发送短信
        $code = mt_rand(1000, 9999);
        if (isset($data['smsSign']) && $data['smsSign'] != 'sudaizhijia') //非速贷之家短信
        {
            $data['message'] = "验证码：{$code}，此验证码十分钟后失效，请勿泄露给他人";

        } else //速贷之家短信
        {
            $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        }

        //签名
        $data['sign'] = SmsStrategy::getSmsSignByAppname($data);
        $data['code'] = $code;
        $re = SmsService::i()->to($data);
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();
        SmsFactory::putSmsCodeToCache('forget_password_code_' . $data['mobile'], $code);
        SmsFactory::putSmsCodeToCache('forget_password_random_' . $data['mobile'], $random);
        if (!$re) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1205), 1205);
        }
        return RestResponseFactory::ok($random);
    }

    /**
     * 手机号短信验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function phone(Request $request)
    {
        $data = $request->all();
        $data['mobile'] = $request->input('mobile');
        //渠道
        $data['channel_fr'] = $request->input('channel_fr', 'channel_2');
        //渠道信息
        $deliverys = DeliveryFactory::getDeliveryByNid($data['channel_fr']);
        $data['channel_id'] = $deliverys ? $deliverys['id'] : '';
        $data['channel_title'] = $deliverys ? $deliverys['title'] : '';
        $data['channel_nid'] = $deliverys ? $deliverys['nid'] : '';

        //验证短信1分钟之内不能重复发送
        $not_exprise = SmsFactory::checkCodeExistenceTime($data['mobile'], 'phone');
        if (!$not_exprise) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1201), 1201);
        }

        $code = mt_rand(1000, 9999);
        $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        $data['code'] = $code;
        $re = SmsService::i()->to($data);
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();
        SmsFactory::putSmsCodeToCache('mobile_code_' . $data['mobile'], $code);
        SmsFactory::putSmsCodeToCache('mobile_random_' . $data['mobile'], $random);
        if (!$re) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1205), 1205);
        }
        return RestResponseFactory::ok($random);
    }

    /**
     * 修改手机号 —— 短信验证码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhone(Request $request)
    {
        $data = $request->all();
        $data['mobile'] = $request->input('mobile');
        //渠道
        $data['channel_fr'] = $request->input('channel_fr', 'channel_2');
        //渠道信息
        $deliverys = DeliveryFactory::getDeliveryByNid($data['channel_fr']);
        $data['channel_id'] = $deliverys ? $deliverys['id'] : '';
        $data['channel_title'] = $deliverys ? $deliverys['title'] : '';
        $data['channel_nid'] = $deliverys ? $deliverys['nid'] : '';

        //验证短信1分钟之内不能重复发送
        $not_exprise = SmsFactory::checkCodeExistenceTime($data['mobile'], 'updatephone');
        if (!$not_exprise) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1201), 1201);
        }

        //验证手机号是否已存在
        $userinfo = UserFactory::getIdByMobile($data['mobile']);
        if ($userinfo) {
            //手机号已注册
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1115), 1115);
        }

        $code = mt_rand(1000, 9999);
        $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        $data['code'] = $code;
        $re = SmsService::i()->to($data);
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();
        SmsFactory::putSmsCodeToCache('update_mobile_code_' . $data['mobile'], $code);
        SmsFactory::putSmsCodeToCache('update_mobile_random_' . $data['mobile'], $random);
        if (!$re) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1205), 1205);
        }
        return RestResponseFactory::ok($random);
    }

    /**
     * 落地页根据手机号是否注册进行不同操作
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        $data['mobile'] = $request->input('mobile');

        //验证手机号是否已存在
        $userinfo = UserFactory::getIdByMobileAndStatus($data['mobile']);
        if ($userinfo) {
            //手机号已注册
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1115), 1115);
        } else {
            //手机号未注册
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1124), 1124);
        }
    }

}
