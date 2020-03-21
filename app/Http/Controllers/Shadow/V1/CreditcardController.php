<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Constants\CreditcardConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Shadow\Creditcard\DoShadowCreditcardApplyHandler;
use App\Models\Factory\BanksFactory;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\CreditcardTypeFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Strategies\BanksStrategy;
use App\Strategies\CreditcardStrategy;
use App\Strategies\CreditcardTypeStrategy;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

/**
 * 马甲信用卡模块
 * Class CreditcardController
 * @package App\Http\Controllers\Shadow\V1
 */
class CreditcardController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡筛选列表表头
     */
    public function fetchSelectTitles(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //不需要提醒
        $data['is_remind'] = 0;
        //所有含有信用卡的银行
        $banks[] = ['id' => 0, 'bank_short_name' => '银行'];
        $bankLists = BanksFactory::fetchHasCreditcardBanks($data);
        $select['banks'] = BanksStrategy::getHasCreditcardBanks($banks, $bankLists);
        //用途
        $usage[] = ['id' => 0, 'name' => '用途', 'type_nid' => ''];
        $usageLists = CreditcardTypeFactory::fetchUsageType();
        $select['usage'] = CreditcardTypeStrategy::getUsageType($usage, $usageLists);
        //等级
        $select['degree'] = CreditcardConstant::SELECT_DEGREE;
        //年费
        $fee[] = ['id' => 0, 'name' => '年费', 'type_nid' => ''];
        $feeLists = CreditcardTypeFactory::fetchFeeType();
        $select['fee'] = CreditcardTypeStrategy::getUsageType($fee, $feeLists);

        return RestResponseFactory::ok($select);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡筛选
     */
    public function fetchCreditCardSearches(Request $request)
    {
        //所有参数
        $data = $request->all();
        $data['usageTypeNid'] = $request->input('usageTypeNid', '');
        $data['degree'] = $request->input('degree', '');
        $data['feeTypeNid'] = $request->input('feeTypeNid', '');
        $data['bankId'] = $request->input('bankId', '');
        $data['shadowNid'] = $request->input('shadowNid', '');

        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //用途筛选
        $usageId = CreditcardTypeFactory::fetchUsageIdByTypeNid($data['usageTypeNid']);
        //用途筛选对应的信用卡id
        $data['usageCreditcardId'] = CreditcardTypeFactory::fetchCreditcardIdByUsageId($usageId);

        //等级筛选
        $data['degreeCreditcardId'] = CreditcardTypeFactory::fetchCreditcardIdByDegreeId($data['degree']);

        //费率筛选
        $data['feeId'] = CreditcardTypeFactory::fetchFeeIdByTypeNid($data['feeTypeNid']);

        //信用卡筛选
        $creditcards = CreditcardFactory::fetchCreditCardSearches($data);
        $pageCount = $creditcards['pageCount'];

        //银行信息
        $banks = BanksFactory::fetchBanksById($data['bankId']);
        $banks = empty($banks) ? [] : BanksStrategy::getBanksById($banks);

        if (!$creditcards['list'] && !$banks) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据转化
        $creditcards = CreditcardStrategy::getShadowSearches($creditcards['list'], $data);

        $searches['banks'] = !empty($banks) ? $banks : RestUtils::getStdObj();
        $searches['list'] = $creditcards;
        $searches['pageCount'] = $pageCount;

        return RestResponseFactory::ok($searches);

    }

    /**
     * 马甲 - 信用卡
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcard(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //马甲唯一标识
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');

        //马甲唯一标识获取马甲id
        $data['shadowId'] = ShadowFactory::getShadowIdByNid($data['shadowNid']);
        //信用卡模块数据
        $banConId = CreditcardFactory::fetchShadowConfigTypeIdByNid($data['shadowId']);
        $creditcard = CreditcardFactory::fetchShadowConfigInfoByTypeId($banConId);
        //用户实名
        $realname = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        if ($creditcard) //信用卡信息存在
        {
            //用户是否进行虚假实名
            $fakeRealname = UserIdentityFactory::fetchFakeUserRealInfo($data['userId'], $creditcard['type_nid']);
            $creditcard['is_realname'] = $realname ? 1 : 0;
            if ($realname || $fakeRealname) $creditcard['is_user_fake_realname'] = 1;
            else $creditcard['is_user_fake_realname'] = 0;
        }

        return RestResponseFactory::ok($creditcard);
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
        //马甲唯一标识
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');

        //马甲唯一标识获取马甲id
        $data['shadowId'] = ShadowFactory::getShadowIdByNid($data['shadowNid']);
        //信用卡配置详情
        $data['config'] = CreditcardFactory::fetchShadowCreditcardConfigInfoById($data['configId']);
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

        $res = new DoShadowCreditcardApplyHandler($data);
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

}