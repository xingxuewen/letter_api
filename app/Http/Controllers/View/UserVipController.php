<?php

namespace App\Http\Controllers\View;

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

        //
        $params = [];
        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($data['userId']);
        //用户名格式处理
        $params['user'] = UserStrategy::replaceUsernameSd($user);
        $params['user']['mobile'] = $user['mobile'];
        $params['user']['token'] = $data['token'];

        //会员、非会员产品相差个数
        $data['vip_diff_count'] = ProductFactory::fetchVipProductDiffCounts();
        //会员八大特权 vip_privilege_upgrade
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

        //区分会员、非会员
        //会员展示使用内容、非会员展示会员动态
        //闪信报告价格
        $data['report_price'] = UserReportFactory::fetchReportPrice();
        $params['dynamics'] = UserVipFactory::fetchUserVipAgain($data);

        //用户是否是会员
        $is_vip = UserVipFactory::getUserVip($data['userId']);
        //会员福利广告
        //会员中心广告type_id
        $typeNid = BannersConstant::BANNER_WELFARE_VIP_CENTER;
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        if ($is_vip) //会员
        {
            return view('app.sudaizhijia.users.vip.center.vip_center', ['data' => $params, 'banners' => $resLists]);
        } else //非会员
        {
            return view('app.sudaizhijia.users.vip.center.common_center', ['data' => $params]);
        }

    }

    /**
     * 会员服务协议地址
     *
     * @return \Illuminate\View\View
     */
    public function fetchVipAgreement()
    {
        return view('app.sudaizhijia.agreements.membership_agreement');
    }
}
