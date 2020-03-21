<?php

namespace App\Http\Controllers\V3;

use App\Constants\CreditConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\DataProductExposureFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductSearchFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * Class ProductSearchController
 * @package App\Http\Controllers\V2
 * 产品搜索
 */
class ProductSearchController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * 产品搜索热词列表 与会员有关
     * 添加产品范围为已解锁产品
     */
    public function fetchHots(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //解锁标识
        $data['unlockNid'] = $request->input('unlockNid', 'banner');
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');

        //是否是会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        if ($data['userVipType']) {
            //会员
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //用户连登天数
        $data['login_count'] = UserFactory::fetchUserUnlockNumById($data['userId']);
        $data['unlock_data'] = ProductStrategy::getUnlockProductIds($data);
        //获取连登解锁产品与可查看产品的交集
        $unlockProIds = ProductFactory::fetchUnlockProducts($data);

        $data['productVipIds'] = $unlockProIds ? array_intersect($unlockProIds, $productVipIds) : [];

        if ($data['userVipType']) $data['productVipIds'] = ProductStrategy::getVipProIds($data, $data['productVipIds']);

        //限制个数
        $data['limit'] = 16;
        $hots = ProductSearchFactory::fetchHotsAboutVip($data);
        //暂无数据
        if (empty($hots)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($hots);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 搜索列表 与会员有关
     */
    public function fetchSearchs(Request $request)
    {
        //搜索条件
        $productName = $request->input('product_name', '');
        $data['productName'] = trim($productName);
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['productType'] = $request->input('productType', 1);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $deviceId = $request->input('deviceId', '');

        //是否是会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        if ($data['userVipType']) {
            //会员
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //记录搜索流水
        $user = UserFactory::fetchUserNameAndMobile($data['userId']);
        $searchLog = ProductSearchFactory::createSearchLog($user, $data);

        //搜索范围
        $searchs = ProductSearchFactory::fetchSearchsAboutVip($data);
        //暂无产品
        if (empty($searchs['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //最大页数
        $pageCount = $searchs['pageCount'];
        //标签
        $productLists = ProductFactory::tagsLimitOneToProducts($searchs['list']);
        $datas['list'] = $productLists;
        $datas['productType'] = $data['productType'];
        //曝光统计
        if (!empty($productLists)) {
            $exposureData['user_id'] = $data['userId'];
            $exposureData['device_id'] = $deviceId;
            $exposureData['product_list'] = implode(',', array_column($productLists, 'platform_product_id'));
            DataProductExposureFactory::AddExposure($exposureData);
        }

        //处理数据
        $lists['list'] = ProductStrategy::getProductOrSearchLists($datas);
        $lists['pageCount'] = $pageCount;

        return RestResponseFactory::ok($lists);
    }
}