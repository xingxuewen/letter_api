<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-8-2
 * Time: 上午10:48
 */

namespace App\Http\Controllers\V2;

use App\Constants\BannersConstant;
use App\Constants\PopConstant;
use App\Constants\UserConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Helpers\RestResponseFactory;
use App\Models\Factory\PopupFactory;
use App\Models\Factory\DeliveryFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\Factory\PushFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\BannerStrategy;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;

/**
 * Class PushController
 * @package App\Http\Controllers\V2
 * 推送
 */
class PushController extends Controller
{
    /**
     * 获取极光推送的registration_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchJpushRegId(Request $request)
    {
        $data['registration_id'] = $request->input('registrationId', 0);
        $data['user_id'] = empty($request->user()->sd_user_id) ? 0 : $request->user()->sd_user_id;
        $data['type'] = PushStrategy::getPlatformType();
        $data['agent'] = UserAgent::i()->getUserAgent();
        $result = PushFactory::addJpushInfo($data);
        if ($result) {
            return RestResponseFactory::ok();
        }

        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2101), 2101);
    }


    /**
     * 批量弹窗
     * 0  首页  1  我的  2  积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPopUp(Request $request)
    {
        $data['position'] = $request->input('position');
        //查询需要推送的信息
        $data['versionCode'] = PopConstant::PUSH_VERSION_CODE_ONELOAN;
        $push = PushFactory::fetchPopups($data);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }

        $pushArr = PushStrategy::getPopups($push);

        return RestResponseFactory::ok($pushArr);
    }

    /**
     * 修改点击次数统计 & 弹窗统计流水
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePopupCount(Request $request)
    {
        $pushId = $request->input('pushId');
        $data['deviceId'] = $request->input('deviceId', '');
        $data['shadow_nid'] = $request->input('shadow_nid', '');
        $data['app_name'] = $request->input('app_name', '');
        $userId = $request->user()->sd_user_id;
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取弹窗信息
        $description = PopupFactory::fetchPopupData($pushId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //执行次数叠加
        $res = PushFactory::updatePopup($pushId);
        //添加弹窗统计流水
        $popup = PopupFactory::createPopupApplyLog($data, $description, $userId, $userArr, $deliveryId, $deliveryArr);

        if ($res || $popup) {
            return RestResponseFactory::ok(RestUtils::getStdObj());
        }
        //出错了，请刷新重试
        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
    }

    /**
     * 引导页 根据手机像素大小 返回相应大小的图片
     * @is_default 默认, 1是 0否
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchGuidePage(Request $request)
    {
        $data['height'] = $request->input('height', 1);
        $data['width'] = $request->input('width', 1);
        //标识 4 表示引导页广告
        $data['position'] = PopConstant::GUIDE_PAGE_BANNERS_TYPE;
        //筛选条件
        $data['version_code'] = PopConstant::PUSH_VERSION_CODE_WECHAT;
        //比例对应的图片
        //默认值
        $push = PushFactory::fetchGuidePageByType($data);
        if (!$push) {
            //查询一张默认的图片
            $data['is_default'] = 1;
            $push = PushFactory::fetchGuidePageByIsDefault($data);
        }
        if (!$push) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //执行次数叠加
        PushFactory::updatePopup($push['id']);
        //数据处理
        $pushs = PushStrategy::getPopup($push);

        return RestResponseFactory::ok($pushs);
    }

    /**
     * V2 连登弹窗
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUnlockLoginPopup(Request $request)
    {
        if (isNewVersion()) {
            //$controller = new \App\Http\Controllers\V9\ProductController();
            //return $controller->seriesLoginWindow($request);
        }

        $data = $request->all();
        $bannerId = $request->input('unlockLoginId', '');//325之后取unlock表新数据
        $data['userId'] = $request->user()->sd_user_id ?? '';
        $data['terminalType'] = $request->input('terminalType') ?? '';
        $data['deviceNum'] = $request->input('deviceId') ?? '';//用来对产品进行地域过滤

        //用户所在渠道
        $data['delivery_id'] = empty($data['userId']) ? 0 : UserFactory::fetchDeliveryIdByUserId($data['userId']);
        //用户所在渠道状态标识
        $data['delivery_sign'] = 1;
        //判断用户是否是新用户
        $isNewUser = UserFactory::fetchUserIsNew($data['userId']);
        //判断用户是否是会员
        $isVip = UserVipFactory::checkIsVip($data);
        //用户最大连登天数
        $user = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);
        //用户最大连登天数 相对于当前球
        $userUnlockLoginCount = UserFactory::fetchUserLoginCountByNow($data['userId']);
        //用户累计登录天数
        //$loginTotal = UserFactory::fetchUserUnlockLoginLogTotalByUserId($data['userId']);
        //会员产品数
        //$vipProCount = ProductFactory::fetchVipProductDiffCounts325($data);
        //会员用户、【连登3天】已解锁的非会员用户 展示累计登录天数


        //****运营维护一套产品，但标识可以不同，则标识相关信息取325，产品相关数据取旧版****
        $bannerTypeId325 = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE_325);
        $bannerTypeId = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE);
        //广告连登解锁信息
        $unlocks325 = BannersFactory::fetchBannerUnlockLoginByTypeId325($bannerTypeId325);
        $unlocksOld = BannersFactory::fetchBannerUnlockLoginByTypeId($bannerTypeId);
        $unlocksOld = array_column($unlocksOld, 'id', 'position');
        //325版本映射旧版
        foreach ($unlocks325 as &$item) {
            $item['mapid'] = $unlocksOld[$item['position']];
        }
        unset($item);
        $mapUnlockLoginId = array_column($unlocks325, 'mapid', 'id');


        //获取连登123各$bannerId条件产品数
        $data['banner_unlock_type_id'] = $bannerTypeId;
        $unlockLoginProductNums = BannerStrategy::getUnlockLoginProductNums($data);

        if ($isVip || (isset($user['login_count']) && $user['login_count'] >= UserConstant::USER_CONTINUE_LOGIN_DAYS)) {
            // 可查看解锁产品数 连登1天产品数量+连登2天产品数量+连登3天产品数量
            //$unlockProCount = array_sum($unlockLoginProductNums);
            // VIP可查看解锁产品数 会员产品数量+连登1天产品数量+连登2天产品数量+连登3天产品数量
            //$unlockProCount = $isVip ? bcadd($vipProCount, $unlockProCount) : $unlockProCount;
            //用户累计登录天数  您已累计登录X天，共解锁Y款产品
            $user['product_list_desc'] = '连续登录可解锁更多产品';
            $user['need_login_count'] = 0;
            //下一期解锁产品数
            $user['unlock_pro_num'] = 0;
            //判断是否展示速贷大全顶部文案 【0不展示，1展示】
            $user['is_show_desc'] = PushStrategy::getIsShowDescToProductList($isNewUser, $isVip);
            $user['login_count'] = $userUnlockLoginCount;
        } else {
            if ($bannerId) {
                $bannerUnlock = BannersFactory::fetchBannerUnlockLoginById($bannerId);
            } else {
                $position = [];
                $position['type_id'] = $bannerTypeId325;
                $position['login_count'] = isset($user['login_count']) ? $user['login_count'] : 0;
                //根据用户最大连登天数，判断下一期展示产品
                $bannerUnlock = BannersFactory::fetchBannerUnlockLoginByDesc($position);
            }

            $user['need_login_count'] = intval(bcsub($bannerUnlock['unlock_day'], $userUnlockLoginCount));
            //本期解锁产品数
            $user['unlock_pro_num'] = $unlockLoginProductNums[$mapUnlockLoginId[$bannerId ?: $bannerUnlock['id']]] ?? 0;
            // 产品列表置顶描述 再连登N天，解锁Q1款产品  Q1=本期对应解锁产品数
            $user['product_list_desc'] = '连续登录可解锁更多产品';
            //判断是否展示速贷大全顶部文案 【0不展示，1展示】
            $user['is_show_desc'] = 1;
            $user['login_count'] = $userUnlockLoginCount;
        }

        return RestResponseFactory::ok($user);
    }
}
