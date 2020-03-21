<?php

namespace App\Http\Controllers\V1;

use App\Constants\BannersConstant;
use App\Constants\SpreadConstant;
use App\Constants\UserConstant;
use App\Constants\UserVipConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\NewsFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\PushFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\BannerStrategy;
use App\Strategies\NewStrategy;
use App\Strategies\PushStrategy;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;
use App\Models\ComModelFactory;

/**
 * Default controller for the `api` module
 */
class BannersController extends Controller
{

    /**
     * 首页广告
     */
    public function banners(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr', 'channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        //广告type_id
        $typeNid = BannersConstant::BANNER_TYPE_BANNER; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 广告点击流水统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBannerLog(Request $request)
    {
        $data = $request->all();
        $data['deviceId'] = $request->input('deviceId', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['bannerId'] = $request->input('bannerId', '');
        $data['shadowNid'] = $request->input('shadowNid', 'sudaizhijia');

        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //根据bannerId查询banner信息
        $data['banner'] = BannersFactory::fetchBannerById($data['bannerId']);
        $log = BannersFactory::createBannerLog($data, $deliverys);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 首页分类专题
     * ad_num = 1 分类专题
     */
    public function fetchSpecials(Request $request)
    {
        $typeId = $request->input('adNum');

        if ($typeId == 1) {
            $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_SPECIAL;
        } elseif ($typeId == 2) {
            $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_HOT_LOAN;
        } else {
            $typeNid = '';
        }

        //类别是否存在，大类别不存在即整类都不存在
        $status = 1; //显示
        $realTypeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        $adNum = $realTypeId;

        if ($adNum == 2) {
            $cashData = BannersFactory::fetchCashBannersNoStatus($adNum);
            //判断产品是否下线 下线修改状态
            BannersFactory::updateBannerCreditCardStatus($cashData);

        }
        //重新查询产品数据
        $cashData = BannersFactory::fetchCashBanners($adNum);

        //热门贷款节日图片
        $hotImg = BannersFactory::fetchBannerConfig();
        //数据处理
        $cashData = BannerStrategy::getCashBanners($cashData, $hotImg);

        return RestResponseFactory::ok($cashData);
    }

    /**
     * ad_num = 2 热门贷款[速贷推荐]
     */
    public function fetchRecommends()
    {
        $adNum = 2;
        $cashData = BannersFactory::fetchCashBanners($adNum);
        //判断产品是否下线 下线修改状态
        BannersFactory::updateBannerCreditCardStatus($cashData);

        //重新查询产品数据
        $cashDataNew = BannersFactory::fetchCashBanners($adNum);
        //热门贷款节日图片
        $hotImg = BannersFactory::fetchBannerConfig();
        //数据处理
        $cashDataNew = BannerStrategy::getCashBanners($cashDataNew, $hotImg);

        return RestResponseFactory::ok($cashDataNew);
    }

    /**
     * @param Request $request
     * @return mixed
     * banner 中 newsid 所对应的资讯详情 [App使用]
     */
    public function fetchNewinfoById(Request $request)
    {
        $newsId = $request->input('newsId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //资讯详情
        $detailLists = NewsFactory::fetchDetails($newsId);
        if (empty($detailLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //点击量统计
        NewsFactory::fetchClicks($newsId);

        //数据处理
        $detailLists = NewStrategy::getBannerNewsById($detailLists);

        //收藏
        if (!empty($userId)) {
            $detailLists['sign'] = NewsFactory::collectionOne($newsId, $userId);
        }

        return RestResponseFactory::ok($detailLists);
    }

    /**
     * 启动页广告
     */
    public function launchAdvertisement()
    {
        //启动页的位置
        $position = 3;
        //查询需要推送的信息
        $push = PushFactory::fetchPopup($position);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }
        //执行次数叠加
        PushFactory::updateDoCounts($push['id']);
        $pushArr = PushStrategy::getPopup($push);
        return RestResponseFactory::ok($pushArr);
    }

    /**
     * 首页速贷推荐  跳转规则与banner一致
     */
    public function fetchSubjects()
    {
        $typeNid = BannersConstant::BANNER_TYPE_RECOMMEND; // 代表速贷推荐
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
     * 账单导入广告图片地址
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBillBanners()
    {
        $typeNid = BannersConstant::BANNER_BILL_IMPORT; // 代表账单导入
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
        $typeNid = BannersConstant::BANNER_TYPE_VIP_CENTER;
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 置顶分类专题
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecialTops()
    {
        //置顶分类专题
        $typeNid = BannersConstant::BANNER_SPECIAL_TOP;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $datas['type_id'] = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $datas['limit'] = 2;
        $specials = BannersFactory::fetchCashBannersByLimit($datas);

        //暂无数据
        if (empty($specials) || empty($datas['type_id'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $specials['list'] = $specials;
        //数据处理
        $cashData = BannerStrategy::getSpecialTops($specials);

        return RestResponseFactory::ok($cashData);
    }

    /**
     * 异形广告
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
        $typeNid = BannersConstant::BANNER_SPECIAL_SHAPED; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 极速贷广告
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchQuickLoanBanners()
    {
        //广告type_id
        $typeNid = BannersConstant::BANNER_QUICKLOAN_TYPE; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 置顶推荐
     * 分类专题 + 一键大额贷
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRecommendTops(Request $request)
    {
        //分类专题两个推荐logo
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';

        //置顶分类专题
        $typeNid = BannersConstant::BANNER_SPECIAL_TOPS;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $datas['type_id'] = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $datas['limit'] = 2;
        $specials['list'] = BannersFactory::fetchCashBannersByLimit($datas);

        //一键贷大额
        $typeNid = SpreadConstant::SPREAD_CONFIG_TYPE_SDZJ;
        $typeId = UserSpreadFactory::fetchConfigTypeIdByNid($typeNid);
        $params['spread'] = UserSpreadFactory::fetchConfigInfoByTypeId($typeId);
        //用户实名
        $realname = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        $spread = $params['spread'] ? $params['spread'] : [];
        if ($spread) {
            //用户是否进行虚假实名
            $fakeRealname = UserIdentityFactory::fetchFakeUserRealInfo($data['userId'], $spread['type_nid']);
            $spread['is_realname'] = $realname ? 1 : 0;
            if ($realname || $fakeRealname) $spread['is_user_fake_realname'] = 1;
            else $spread['is_user_fake_realname'] = 0;
        }

        //数据处理
        $resData = BannerStrategy::getSpecialTops($specials);
        //一键大额贷数据处理
        if ($spread) $spread = SpreadStrategy::getSpreadTops($spread);

        $resData['item'] = $spread ? $spread : RestUtils::getStdObj();

        return RestResponseFactory::ok($resData);
    }

    /**
     * 首页广告解锁连登
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBannerUnlockLogins(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //解锁连登类型唯一标识
        $typeNid = BannersConstant::BANNER_UNLOCK_LOGIN_TYPE;
        $typeId = BannersFactory::fetchUnlockLoginTypeIdByNid($typeNid);
        //用户最大连登天数
        $userLogin = UserFactory::fetchUserUnlockLoginsByUserId($userId);
        //会员独家类型唯一标识
        $vipTypeNid = BannersConstant::BANNER_TYPE_MEMBERSHIP;
        $vipTypeId = BannersFactory::fetchTypeId($vipTypeNid);
        //用户是否是会员
        $data['userId'] = $userId;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);

        //广告连登解锁信息
        $unlocks = BannersFactory::fetchBannerUnlockLoginByTypeId($typeId);
        if ($unlocks) {
            // 兼容，只登前3个，连登123
            if (count($unlocks) > 3) {
                array_pop($unlocks);
            }
            //数据处理
            $unlocks = BannerStrategy::fetchBannerUnlockLogin($unlocks, $userLogin, $data);
        }

        //会员独家信息
        $membershiops = BannersFactory::fetchSubjects(1, $vipTypeId);
        if ($membershiops) {
            //数据处理
            $membershiops = BannerStrategy::getBanners($membershiops);
        }

        $datas['unlocks'] = empty($unlocks) ? [] : $unlocks;
        $datas['memberships'] = empty($membershiops[0]) ? RestUtils::getStdObj() : $membershiops[0];
//        $datas['login_count'] = empty($userLogin) ? 0 : $userLogin['login_count'];

        return RestResponseFactory::ok($datas);
    }

    /**
     * 连登广告点击流水
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createBannerUnlockLoginLog(Request $request)
    {
        $data = $request->all();
        $bannerId = $request->input('unlockLoginId');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['deviceNum'] = $request->input('deviceId', '');
        $data['shadowNid'] = $request->input('shadowNid', 'sudaizhijia');
        $data['clickSource'] = $request->input('clickSource', '');

        //查询当前解锁连登广告信息
        $data['bannerUnlock'] = BannersFactory::fetchBannerUnlockLoginById($bannerId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $data['deliverys'] = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //统计流水
        $log = BannersFactory::createBannerUnlockLoginLog($data);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}
