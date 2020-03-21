<?php

namespace App\Http\Controllers\V1;

use App\Constants\BannersConstant;
use App\Constants\PopConstant;
use App\Constants\UserConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\PushFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;

/**
 * Class PushController
 * @package App\Http\Controllers\V1
 * 推送
 */
class PushController extends Controller
{
    /**
     * 极光推送 —— 接收用户指定设备的registrationId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function putRegistrationIdToCache(Request $request)
    {
        $registrationId = $request->input('registrationId');
        $userId = $request->user()->sd_user_id;
        //存redis
        CacheFactory::putValueToCache('jpush_registration_id_' . $userId, $registrationId);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * 任务弹窗
     * 0  首页  1  我的  2  积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPopup(Request $request)
    {
        $position = $request->input('position');
        //查询需要推送的信息
        $push = PushFactory::fetchPopup($position);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }

        //执行次数叠加
        PushFactory::updatePopup($push['id']);

        $pushArr = PushStrategy::getPopup($push);

        return RestResponseFactory::ok($pushArr);
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
        $data['version_code'] = PopConstant::PUSH_VERSION_CODE_DEFAULT;
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
     * 连登弹窗
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUnlockLoginPopup(Request $request)
    {
        $data = $request->all();
        $bannerId = $request->input('unlockLoginId', '');
        $data['userId'] = $request->user()->sd_user_id;

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
        //$vipProCount = ProductFactory::fetchVipProductDiffCounts();
        //会员用户、【连登3天】已解锁的非会员用户 展示累计登录天数
        $bannerTypeId = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE);
        if ($isVip || (isset($user['login_count']) && $user['login_count'] >= UserConstant::USER_CONTINUE_LOGIN_DAYS)) {
            //共解锁产品数
            //$unlockProCount = BannersFactory::fetchBannerUnlockProCountByTypeId($bannerTypeId);
            //会员用户、【连登3天】已解锁的非会员用户
            // 可查看解锁产品数 会员产品数量+连登1天产品数量+连登2天产品数量+连登3天产品数量
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
                $position['type_id'] = $bannerTypeId;
                $position['login_count'] = isset($user['login_count']) ? $user['login_count'] : 0;
                //根据用户最大连登天数，判断下一期展示产品
                $bannerUnlock = BannersFactory::fetchBannerUnlockLoginByDesc($position);
            }
            $user['need_login_count'] = intval(bcsub($bannerUnlock['unlock_day'], $userUnlockLoginCount));
            //本期解锁产品数
            $user['unlock_pro_num'] = isset($bannerUnlock['unlock_pro_num']) ? $bannerUnlock['unlock_pro_num'] : 0;
            // 产品列表置顶描述 再连登N天，解锁Q1款产品  Q1=本期对应解锁产品数
            $user['product_list_desc'] = '连续登录可解锁更多产品';
            //判断是否展示速贷大全顶部文案 【0不展示，1展示】
            $user['is_show_desc'] = 1;
            $user['login_count'] = $userUnlockLoginCount;
        }

        return RestResponseFactory::ok($user);

    }

    /**
     * 连登解锁规则弹窗
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUnlockRulePopup()
    {
        $rule = '<p style="font-size: 26px;color: #333;line-height: 36px;font-weight: 400;">
               ① 每位用户从注册第2日开始即可通过连续登录解锁新产品；<br/>
               ② 连续登录1天即可解锁连登1天产品；<br/>
               ③ 连续登录2天可解锁连登2天产品，但间断登录要重新开始；<br/>
               ④ 连续登录3天可解锁连登3天产品，但间断登录要重新开始；<br/>
               ⑤ 会员用户可立即解锁全部产品；<br/>
               ⑥ 举例：用户第1天注册成功，第3天该用户再次登录，此时用户可解锁连登1天产品；第4天该用户未登录，第5天、第6天该用户连续登录2天，那么第6日该用户登录后即可解锁连登2天产品；
           </p>';

        $rules['rule'] = Utils::removeHtmlNtr($rule);

        return RestResponseFactory::ok($rules);
    }
}