<?php

namespace App\Http\Controllers\V3;

use App\Constants\BannersConstant;
use App\Constants\CreditcardConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\ComModelFactory;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\UserIdentityFactory;
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
        $typeNid = BannersConstant::BANNER_TYPE_BANNER_CAROUSEL; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 首页分类专题
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecialsAndRecommends()
    {
        //分类专题
        $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_THIRD_EDITION_SPECIAL;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $typeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $specials = BannersFactory::fetchCashBanners($typeId);

        //暂无数据
        if (empty($specials) || empty($typeId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $cashData = BannerStrategy::getSpecialsAndRecommends($specials);

        return RestResponseFactory::ok($cashData);
    }

    /**
     * 会员中心广告
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchVipCenterBanner()
    {
        //会员福利广告
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
     * 更换图片
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
        //信用卡类型标识
        $data['banConNid'] = $request->input('banConNid', CreditcardConstant::BANNER_CREDITCARD_TYPE_SDZJ);

        //置顶分类专题
        $typeNid = BannersConstant::BANNER_SPECIAL_TOP_V2;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $datas['type_id'] = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $datas['limit'] = 2;
        $specials['list'] = BannersFactory::fetchCashBannersByLimit($datas);

        //信用卡模块数据
        $banConNid = $data['banConNid'];
        $banConId = CreditcardFactory::fetchConfigTypeIdByNid($banConNid);
        $creditcard = CreditcardFactory::fetchConfigInfoByTypeId($banConId);
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
        //数据处理
        $cashData = BannerStrategy::getSpecialTops($specials);
        //信用卡无数据处理
        $cashData['creditcard'] = $creditcard ? $creditcard : RestUtils::getStdObj();

        return RestResponseFactory::ok($cashData);
    }

}