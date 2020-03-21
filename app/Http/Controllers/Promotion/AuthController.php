<?php

namespace App\Http\Controllers\Promotion;

use App\Events\V1\UserLoginEvent;
use App\Events\V1\UserRegEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Promotion\Login\DoPromotionLoginHandler;
use App\Models\Chain\Promotion\Register\DoPromotionRegisterHandler;
use App\Models\Factory\InviteFactory;
use App\Models\Factory\UserFactory;
use App\Services\Core\Promotion\Sudai\SudaiService;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;

/**
 * 推广联登登录相关
 *
 * Class AuthController
 * @package App\Http\Controllers\Promotion
 */
class AuthController extends Controller
{
    /**
     * 推广联登
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $params = $request->all();
        //dd($params);
        //判断传值参数
        if (!isset($params['encrypt_data'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(21000), 21000);
        }
        //rsa解密
        $return = SudaiService::undoData($params['encrypt_data']);
        if (!$return) //解密失败
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(21001), 21001);
        }
        //json_decode 将json串转化为数组
        $data = json_decode($return, true);
        #查库检查用户手机号是否存在
        $user = UserFactory::getUserByMobile($data['mobile']);

        if ($user) {
            #如果用户激活则调用登录责任链
            $login = new DoPromotionLoginHandler($data);
            $re = $login->handleRequest();
            if (isset($re['error'])) {
                if ($re['code'] == 403403) {
                    return RestResponseFactory::forbidden($re['error'], 403, $re['error']);
                }
                return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
            }
            #添加登录事件监听
            $event_data = [];
            event(new UserLoginEvent($event_data));
        } else {
            #如果用户未激活调用注册责任链
            $register = new DoPromotionRegisterHandler($data);
            $re = $register->handleRequest();
            if (isset($re['error'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
            }
            #添加注册邀请好友事件监听
            $invite['mobile'] = $data['mobile'];
            #将当前用户的用户id传入事件
            $invite['invite_user_id'] = UserFactory::getIdByMobile($data['mobile']);
            $invite['user_id'] = isset($data['uid']) ? $data['uid'] : '';
            #判断有无uid传值
            if (!empty($invite['user_id'])) {
                #通过uid邀请的(生成code码记录并赋值给sd_invite_code)
                $invite['sd_invite_code'] = InviteFactory::fetchInviteCode($invite['user_id']);
            } else {
                #原生code码邀请
                $invite['sd_invite_code'] = isset($data['sd_invite_code']) ? $data['sd_invite_code'] : '';
            }
            #添加注册渠道数据事件监听
            $count['channel_fr'] = !empty($data['channel_fr']) ? $data['channel_fr'] : 'channel_2';
            $count['userId'] = $invite['invite_user_id'];
            $count['version'] = isset($data['version']) ? $data['version'] : UserStrategy::version();
            event(new UserRegEvent(['invite' => $invite, 'count' => $count]));
        }

        return RestResponseFactory::ok($re);
    }
}