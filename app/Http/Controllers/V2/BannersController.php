<?php

namespace App\Http\Controllers\V2;

use App\Constants\BannersConstant;
use App\Constants\CreditcardConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\ComModelFactory;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\BannerStrategy;
use Illuminate\Http\Request;

/**
 * Banners
 */
class BannersController extends Controller
{
    /**
     * 首页广告
     */
    public function fetchBanners(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr', 'channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        //广告type_id
        $typeNid = BannersConstant::BANNER_TYPE_NEW_BANNER; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 首页分类专题
     * type_nid = 3  新分类专题
     */
    public function fetchSpecials()
    {
        //type_nid = 3 新分类专题
        $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_NEW_SPECIAL;
        //类别是否存在，大类别不存在即整类都不存在
        $status = 1; //显示
        $typeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $specials = BannersFactory::fetchCashBanners($typeId);

        //暂无数据
        if (empty($specials) || empty($typeId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $cashData = BannerStrategy::getCashBanners($specials, $hotImg = '');

        return RestResponseFactory::ok($cashData);
    }

    /**
     * 第二版 速贷推荐
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSubjects()
    {
        $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_SECOND_EDITION_RECOMMEND; // 代表速贷推荐
        $status = 1; //存在
        $typeId = BannersFactory::fetchTypeId($typeNid);

        //获取速贷专题数据
        $subjects = BannersFactory::fetchSubjects($status, $typeId);
        //暂无数据
        if (empty($subjects)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $subjects = BannerStrategy::getBanners($subjects);

        return RestResponseFactory::ok($subjects);
    }

    /**
     * 会员中心广告
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchVipCenterBanner()
    {
        //会员中心广告type_id
        $typeNid = BannersConstant::BANNER_WELFARE_VIP_CENTER;
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }


    /**
     * 置顶分类专题
     * 信用卡、闪电下款、本周放款王
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecialTops(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';

        //置顶分类专题
        $typeNid = BannersConstant::BANNER_SPECIAL_TOP;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $datas['type_id'] = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $datas['limit'] = 2;
        $specials['list'] = BannersFactory::fetchCashBannersByLimit($datas);

        //信用卡模块数据
        $banConNid = CreditcardConstant::BANNER_CREDITCARD_TYPE_SDZJ;
        $banConId = CreditcardFactory::fetchConfigTypeIdByNid($banConNid);
        $creditcard = CreditcardFactory::fetchConfigInfoByTypeId($banConId);
        //用户实名
        $realname = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        if ($creditcard) { //信用卡信息存在
        //用户是否进行虚假实名
            $fakeRealname = UserIdentityFactory::fetchFakeUserRealInfo($data['userId'], $creditcard['type_nid']);
            $creditcard['is_realname'] = $realname ? 1 : 0;
            if ($realname || $fakeRealname) {
                $creditcard['is_user_fake_realname'] = 1;
            } else {
                $creditcard['is_user_fake_realname'] = 0;
            }
        }
        //数据处理
        $cashData = BannerStrategy::getSpecialTops($specials);
        //信用卡无数据处理
        $cashData['creditcard'] = $creditcard ? $creditcard : RestUtils::getStdObj();

        return RestResponseFactory::ok($cashData);
    }

    /**
     * 异形广告
     * 添加微信小程序入口
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecialShapedBanners(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr', 'channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        //广告type_id
        $typeNid = BannersConstant::BANNER_SPECIAL_SHAPED_V2; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * V2 首页广告解锁连登
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBannerUnlockLogins(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //用户所在渠道
        $data['delivery_id'] = empty($userId) ? 0 : UserFactory::fetchDeliveryIdByUserId($userId);
        //用户所在渠道状态标识
        $data['delivery_sign'] = 1;
        //解锁连登类型唯一标识
        //****运营维护一套产品，但标识可以不同，则标识相关信息取325，产品相关数据取旧版****
        $typeNid325 = BannersConstant::BANNER_UNLOCK_LOGIN_TYPE_325;
        $typeId325 = BannersFactory::fetchUnlockLoginTypeIdByNid($typeNid325);

        $typeNid = BannersConstant::BANNER_UNLOCK_LOGIN_TYPE;
        $typeId = BannersFactory::fetchUnlockLoginTypeIdByNid($typeNid);

        //用户最大连登天数
        $userLogin = UserFactory::fetchUserUnlockLoginsByUserId($userId);
        //会员独家类型唯一标识
        $vipTypeNid = BannersConstant::BANNER_TYPE_MEMBERSHIP;
        $vipTypeId = BannersFactory::fetchTypeId($vipTypeNid);
        //用户是否是会员
        $data['userId'] = $userId;
        $data['terminalType'] = $request->input('terminalType') ?? '';
        $data['deviceNum'] = $request->input('deviceId') ?? '';
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);
        //这里将连登解锁type_id传入
        $data['banner_unlock_type_id'] = $typeId;

        //广告连登解锁信息
        $unlocks325 = BannersFactory::fetchBannerUnlockLoginByTypeId325($typeId325);
        $unlocksOld = BannersFactory::fetchBannerUnlockLoginByTypeId($typeId);
        $unlocksOld = array_column($unlocksOld, 'id', 'position');

        //325版本映射旧版
        foreach ($unlocks325 as &$item) {
            $item['mapid'] = $unlocksOld[$item['position']];
        }
        unset($item);

        if ($unlocks325) {
            //数据处理
            $unlocks = BannerStrategy::fetchBannerUnlockLoginV2($unlocks325, $userLogin, $data);
        }

        //会员独家信息
        $membershiops = BannersFactory::fetchSubjects(1, $vipTypeId);
        if ($membershiops) {
            //数据处理
            $membershiops = BannerStrategy::getBanners($membershiops);
        }

        $datas['unlocks'] = empty($unlocks) ? [] : $unlocks;
        $datas['memberships'] = empty($membershiops[0]) ? RestUtils::getStdObj() : $membershiops[0];

        return RestResponseFactory::ok($datas);
    }
}
