<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserVip\Privilege\DoPrivilegeHandler;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\UserBankCardStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;

/**
 * vip用户模块
 *
 * Class UserVipController
 * @package App\Http\Controllers\V1
 */
class UserVipController extends Controller
{
    /**
     * 会员中心
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberCenter(Request $request)
    {
        $params['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');

        //贷款产品
        $loanVipArr = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $loanVipArr;
        $data['loanVipCount'] = ProductFactory::fetchProductCounts($data);
        $loanComArr = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $loanComArr;
        $data['loanCommonCount'] = ProductFactory::fetchProductCounts($data);

        //下款率
        $data['downCommonRate'] = UserVipConstant::MEMBER_COMMON_DOWN_RATE;
        $data['downVipRate'] = UserVipConstant::MEMBER_VIP_DOWN_RATE;

        //会员动态
        $data['memberActivity'] = UserVipStrategy::getMemberActivityInfo();

        //价格
        $data['totalPrice'] = '￥' . UserVipFactory::getVipAmount() . '/年';
        $data['totalNoPrice'] = '￥' . UserVipConstant::MEMBER_PRICE . '/年';
        $data['totalPriceTime'] = UserVipStrategy::isUserVip($params['userId']);

        //客服电话
        $data['phone'] = UserVipConstant::CONSUMER_HOTLINE;

        return RestResponseFactory::ok($data);
    }

    /**
     * 可用银行卡列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankList(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //根据id显示查询标识
        $data['userBankId'] = $request->input('userBankId', 0);

        //该用户是否含有银行卡
        $bankCount = UserBankCardFactory::fetchUserBanksCount($data['userId']);
        //有银行修改状态
        if ($bankCount != 0) {
            //查询没有上次支付状态则设置一个默认支付状态
            $cardPay = UserBankCardFactory::fetchCardLastPayById($data['userId']);
            if (!$cardPay) {
                //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
                $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($data['userId']);
                if (!$cardLastPay) {
                    return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
                }
            }
        }

        //获取用户的银行列表
        $list = UserBankCardFactory::getUsedCardList($data);
        $pageCount = $list['pageCount'];
        //暂无数据
        if (empty($list['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $data['list'] = $list['list'];
        $reList['list'] = UserBankCardStrategy::getBackBankInfo($data);
        $reList['pageCount'] = $pageCount;

        return RestResponseFactory::ok($reList);
    }


    /**
     * 可用银行卡列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *  by xuyj  v3.2.3
     */
    public function getBankList_new(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //根据id显示查询标识
        $data['userBankId'] = $request->input('userBankId', 0);

        //该用户是否含有银行卡
        $bankCount = UserBankCardFactory::fetchUserBanksCount($data['userId']);
        logInfo("getbanklist_new", $bankCount);

        //有银行修改状态
        if ($bankCount != 0) {
            logInfo("11111111111111 ==============");
            //查询没有上次支付状态则设置一个默认支付状态
            $cardPay = UserBankCardFactory::fetchCardLastPayById($data['userId']);
            if (!$cardPay) {
                //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
                $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($data['userId']);
                if (!$cardLastPay) {
                    return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
                }
            }
        }
        //获取用户的银行列表
        $list = UserBankCardFactory::getUsedCardList_new($data);
        logInfo("22222222222222222 ==============", $list);

        $pageCount = $list['pageCount'];
        //暂无数据
        if (empty($list['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $data['list'] = $list['list'];
        logInfo("huiju pay counbt", $data);
        $reList['list'] = UserBankCardStrategy::getBackBankInfo_new($data,$data['userBankId']);
        $reList['pageCount'] = $pageCount;

        return RestResponseFactory::ok($reList);
    }

    /**
     * 会员中心-普通用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberHome(Request $request)
    {
        //过期年限
        $data['expired'] = UserVipStrategy::getVipYeer();
        $data['member_price'] = UserVipFactory::getVipAmount();
        $data['telephone_num'] = UserVipConstant::CONSUMER_HOTLINE;

        return RestResponseFactory::ok($data);
    }

    /**
     * 会员充值金额
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchVipRecharge(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $is_vip = UserVipFactory::getUserVip($userId);
        //
        $datas = [];
        //价格
        $datas['totalPrice'] = UserVipFactory::getVipAmount() . '/年';
        $datas['totalNoPrice'] = UserVipConstant::MEMBER_PRICE . '/年';
        //单纯显示价格
        $datas['totalPriceNum'] = UserVipFactory::getVipAmount() . '';
        $datas['totalNoPriceNum'] = UserVipConstant::MEMBER_PRICE . '';
        //是否是会员
        $datas['isVipUser'] = $is_vip ? 1 : 0;

        return RestResponseFactory::ok($datas);
    }

    /**
     * 单独获取会员动态
     * 用户名+随机值
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMembershipDynamics(Request $request)
    {
        $data['userId'] = $request->input('uid', '');
        $users = UserVipFactory::getUser($data['userId']);
        if (!empty($users)) {
            $message = UserVipStrategy::getRandMessage(UserVipConstant::DYNAMIC_MESSAGE);
            $userData = UserVipStrategy::getMemberActivityData($data['userId'], $message, $users);;
        } else {
            $userData = [];
        }

        return RestResponseFactory::ok($userData);
    }

    /**
     * 特权跳转地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPrivilegeUrl(Request $request)
    {
        //特权id
        $data['privilegeId'] = $request->input('privilegeId', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //特权详情
        $privilegeInfo = UserVipFactory::getPrivilege($data['privilegeId']);
        //暂无数据
        if (empty($privilegeInfo)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //获取用户手机号
        $user = UserFactory::fetchUserById($data['userId']);
        //数据处理
        $data = UserVipStrategy::getOauthPrivilegeDatas($data, $user, $privilegeInfo);

        //获取地址流程  有对接走对接  没有对接正常返回地址
        $res = new DoPrivilegeHandler($data);
        $re = $res->handleRequest();

        if (isset($re['error'])) //错误提示
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        //返回地址
        $urls['url'] = $re['url'];

        return RestResponseFactory::ok($urls);
    }

}