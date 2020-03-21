<?php

namespace App\Http\Controllers\V2;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;

class UserSpreadController extends Controller
{
    /**
     * 百款聚到 一键贷功能借款信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOneloanInfo(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';

        //用户贷款额度
        $con_money = SpreadConstant::CON_MONEY;
        $datas['money'] = UserSpreadFactory::fetchProductOperateConfigByNid($con_money);
        //速贷大全列表是否携带参数[0否，1是]
        $con_product_param = SpreadConstant::CON_PRODUCT_PARAM;
        $is_product_param = UserSpreadFactory::fetchProductOperateConfigByNid($con_product_param);
        $datas['is_product_param'] = intval($is_product_param);
        //是否清理本地缓存[0否，1是]
        $con_local_cache = SpreadConstant::CON_LOCAL_CACHE;
        $is_local_cache = UserSpreadFactory::fetchProductOperateConfigByNid($con_local_cache);
        $datas['is_local_cache'] = intval($is_local_cache);

        //新增产品数
        //昨天日期
        $data['created_date'] = date('Y-m-d', strtotime('-1 day'));
        $yesProductIds = ProductFactory::fetchDayOnlineProductIds($data);
        //今天日期
        $data['created_date'] = date('Y-m-d', time());
        $dayProductIds = ProductFactory::fetchDayOnlineProductIds($data);
        //ids
        $diffIds = array_diff($dayProductIds, $yesProductIds);
        //分析差值
        $params['diffCount'] = $diffIds ? count($diffIds) : 0;

        //贷款产品
        $data['productIds'] = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $params['counts'] = ProductFactory::fetchProductCounts($data);

        //一键选贷款
        $typeNid = SpreadConstant::SPREAD_CONFIG_TYPE_SDZJ;
        $typeId = UserSpreadFactory::fetchConfigTypeIdByNid($typeNid);
        $params['spread'] = UserSpreadFactory::fetchConfigInfoByTypeId($typeId);
        //用户实名
        $realname = UserIdentityFactory::fetchUserRealInfo($data['userId']);

        $datas['add_product_desc'] = SpreadStrategy::getOneloanInfo($params);
        $spread = $params['spread'] ? $params['spread'] : [];

        if ($spread) {
            //用户是否进行虚假实名
            $fakeRealname = UserIdentityFactory::fetchFakeUserRealInfo($data['userId'], $spread['type_nid']);
            $spread['is_realname'] = $realname ? 1 : 0;
            if ($realname || $fakeRealname) $spread['is_user_fake_realname'] = 1;
            else $spread['is_user_fake_realname'] = 0;
        }

        $datas['spread'] = $spread ? $spread : RestUtils::getStdObj();

        return RestResponseFactory::ok($datas);
    }
}