<?php

namespace App\Http\Controllers\View\V2;

use App\Constants\BannersConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\BannerStrategy;
use App\Strategies\UserStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;

/**
 * 会员页面相关
 *
 * Class UserVipController
 * @package App\Http\Controllers\View
 */
class UserVipController extends Controller
{
    /**
     * 会员中心
     * 一个接口地址
     * 根据用户是否是会员，返回相应的参数
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function vipCenter(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['token'] = isset($request->user()->accessToken) ? $request->user()->accessToken : '';
//      $data['userId'] = '1288';
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //是否续费标识
        $data['isRecharge'] = $request->input('isRecharge', 0);

        //
        $params = [];
        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($data['userId']);
        //用户是否是会员
        $is_vip = UserVipFactory::getUserVip($data['userId']);
        //用户名格式处理
        $params['user'] = UserStrategy::replaceUsernameSd($user);
        $params['user']['mobile'] = $user['mobile'];
        if (isset($data['isRecharge']) && $data['isRecharge'] == 1) //续费 显示非会员页面
        {
            $is_vip = 0;
        }
        $params['user']['is_vip'] = $is_vip ? 1 : 0;
        $params['user']['isRecharge'] = $data['isRecharge'];
        $params['user']['token'] = $data['token'];

        //会员、非会员产品相差个数
        $data['vip_diff_count'] = ProductFactory::fetchVipProductDiffCounts();
        //会员八大特权 vip_privilege_third_upgrade
        //会员类型主id
        $vipTypeId = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID);
        //特权类型主id
        $data['priTypeId'] = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_UPGRADE);
        //根据会员查询对应的特权列表ids
        $data['privilegeIds'] = UserVipFactory::getVipPrivilegeIds($vipTypeId);
        //查询最终特权列表
        $privileges = UserVipFactory::fetchVipPrivileges($data);
        //特权数据处理
        $params['privileges']['list'] = UserVipStrategy::getVipPrivileges($privileges, $data);
        //会员特权总个数
        $params['privileges']['vip_privilege_count'] = count($privileges);

        //非会员的会员动态 + 会员的轮播数据
        //闪信报告价格
        $data['report_price'] = UserReportFactory::fetchReportPrice();
        $dynamics = UserVipFactory::fetchUserVipsThirdUpgrade($data);

        //充值列表
        $recharges = [];
        //会员福利
        $resLists = [];
        //根据会员状态，判断查询页面数据
        if ($is_vip) //会员页面展示数据
        {
            //轮播数据
            $memberActivity = $dynamics;
            //会员福利广告
            //会员中心广告type_id
            $typeNid = BannersConstant::BANNER_VIP_CENTER_WELFARES;
            $typeId = BannersFactory::fetchTypeId($typeNid);
            $bannerLists = BannersFactory::fetchBanners($typeId);
            $resLists = BannerStrategy::getBanners($bannerLists);

        } else //非会员页面展示数据
        {
            //会员动态
            $memberActivity = $dynamics['memberActivity'];
            //充值列表
            $recharges = UserVipFactory::fetchRechargesByTypeId($vipTypeId);
            //充值列表数据处理
            $recharges = UserVipStrategy::getRecharges($recharges);

        }

//        return RestResponseFactory::ok($recharges);

        if ($is_vip) //会员
        {
            return view('app.sudaizhijia.users.vip_v2.center.vip_center', ['data' => $params, 'memberActivity' => $memberActivity, 'banners' => $resLists]);
        } else //非会员
        {
            return view('app.sudaizhijia.users.vip_v2.center.common_center', ['data' => $params, 'memberActivity' => $memberActivity, 'recharges' => $recharges]);
        }

    }
}
