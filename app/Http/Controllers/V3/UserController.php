<?php

namespace App\Http\Controllers\V3;

use App\Constants\CreditConstant;
use App\Constants\UserIdentityConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\CreditStatusFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSignFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\Core\WangYiYunDun\CloudShield\CloudShieldService;
use App\Models\Factory\UserFactory;
use App\Strategies\UserIdentityStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers\V3
 * 用户中心
 */
class UserController extends Controller
{

    function index(Request $request)
    {
        return RestResponseFactory::ok(null, 'OK');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改用户名 加积分
     */
    public function updateUsername(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['username'] = $request->input('username');

        //判断用户名的唯一性
        $username = UserFactory::fetchUsernameByName($data);
        if ($username) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1120), 1120);
        } else {
            $dataId = "opensns";
            $text = CloudShieldService::UserMain($dataId, $data['userId'], $data['username']);
            if ($text != 0) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2102), 2102);
            }
        }

        $res = UserFactory::updateUsername($data);
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //首次设置用户名加积分
        $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_USERNAME_TYPE;
        $eventData['remark'] = CreditConstant::ADD_INTEGRAL_USER_USERNAME_REMARK;
        $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
        $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
        $eventData['userId'] = $data['userId'];
        event(new AddIntegralEvent($eventData));

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 用户账户信息
     * 与活体无关
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUserinfo(Request $request)
    {
        //通過Token獲取用戶ID
        $userId = $request->user()->sd_user_id;
        //实名认证状态值
        $params['realnameType'] = $request->input('realnameType', '');
        $params['userId'] = $userId;


        $data['userId'] = $userId;
        //账号余额
        $data['userAccount'] = AccountFactory::fetchBalance($userId);
        //账号积分
        $data['userScore'] = CreditFactory::fetchCredit($userId);
        //用户信息
        $data['userProfile'] = UserFactory::fetchRealNameAndSex($userId);
        //用户信息
        $data['userAuth'] = UserFactory::fetchUserNameAndMobile($userId);
        //用户头像
        $data['userinfo'] = UserFactory::fetchPhotoById($userId);
        //用户是否签到
        $data['user_sign'] = UserSignFactory::fetchUserSignByUserId($userId);
        //查询身份证认证情况
        $data['alive_status'] = 1;
        $alive = UserIdentityFactory::fetchUserAliveStatusById($data);
        $data['is_alive'] = $alive ? 1 : 0;
        //实名情况
        $data['step'] = UserIdentityStrategy::getRealnameStep($params);
        if ($data['step'] == UserIdentityConstant::AUTHENTICATION_STATUS_FINAL) //完整
        {
            $data['realname'] = empty($alive) ? [] : UserIdentityFactory::fetchIdcardAuthenInfo($userId);

        } else //与活体无关
        {
            $data['realname'] = UserIdentityFactory::fetchIdcardAuthenInfoByStatus($data);
        }

        //查询绑卡情况
        $params['userId'] = $userId;
        $data['bankcardCount'] = UserBankCardFactory::fetchUserBanksCount($params);
        //会员状态
        $vipTypeId = UserVipFactory::getVipTypeId();
        $data['vip'] = UserVipFactory::getVIPInfoByUserId($data['userId']);
        //会员特权个数
        //特权类型主id
        $priData['priTypeId'] = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_UPGRADE);
        //根据会员查询对应的特权列表ids
        $priData['privilegeIds'] = UserVipFactory::getVipPrivilegeIds($vipTypeId);
        $data['vipPrivilegeCount'] = UserVipFactory::fetchVipPrivilegeCount($priData);

        $info = UserStrategy::fetchUserautheninfo($data);

        return RestResponseFactory::ok($info);
    }


    // 用户信息列表
    // by xuyj v3.2.3
    public function fetchUserinfo_new(Request $request)
    {
        //通過Token獲取用戶ID
        $userId = $request->user()->sd_user_id;
        //实名认证状态值
        $params['realnameType'] = $request->input('realnameType', '');
        $params['userId'] = $userId;


        $data['userId'] = $userId;
        //账号余额
        $data['userAccount'] = AccountFactory::fetchBalance($userId);
        //账号积分
        $data['userScore'] = CreditFactory::fetchCredit($userId);
        //用户信息
        $data['userProfile'] = UserFactory::fetchRealNameAndSex($userId);
        //用户信息
        $data['userAuth'] = UserFactory::fetchUserNameAndMobile($userId);
        //用户头像
        $data['userinfo'] = UserFactory::fetchPhotoById($userId);
        //用户是否签到
        $data['user_sign'] = UserSignFactory::fetchUserSignByUserId($userId);
        //查询身份证认证情况
        $data['alive_status'] = 1;
        $alive = UserIdentityFactory::fetchUserAliveStatusById($data);
        $data['is_alive'] = $alive ? 1 : 0;
        //实名情况
        $data['step'] = UserIdentityStrategy::getRealnameStep($params);
        if ($data['step'] == UserIdentityConstant::AUTHENTICATION_STATUS_FINAL) //完整
        {
            $data['realname'] = empty($alive) ? [] : UserIdentityFactory::fetchIdcardAuthenInfo($userId);

        } else //与活体无关
        {
            $data['realname'] = UserIdentityFactory::fetchIdcardAuthenInfoByStatus_new($data);
        }

        //查询绑卡情况
        $params['userId'] = $userId;
        $data['bankcardCount'] = UserBankCardFactory::fetchUserBanksCount($params);
        //会员状态
        $vipTypeId = UserVipFactory::getVipTypeId();
        $data['vip'] = UserVipFactory::getVIPInfoByUserId($data['userId']);
        //会员特权个数
        //特权类型主id
        $priData['priTypeId'] = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_UPGRADE);
        //根据会员查询对应的特权列表ids
        $priData['privilegeIds'] = UserVipFactory::getVipPrivilegeIds($vipTypeId);
        $data['vipPrivilegeCount'] = UserVipFactory::fetchVipPrivilegeCount($priData);

        $info = UserStrategy::fetchUserautheninfo_new($data);

        return RestResponseFactory::ok($info);
    }

}
