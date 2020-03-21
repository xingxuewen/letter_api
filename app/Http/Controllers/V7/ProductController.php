<?php

namespace App\Http\Controllers\V7;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use DB;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V4
 */
class ProductController extends Controller
{
    /**
     * 第七版 产品列表 & 速贷大全筛选
     * 已解锁产品都展示
     * 记录登录天数 解锁产品数
     * 后台默认排序
     * 点击产品置后
     * 曝光度统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function fetchProductsOrSearchs(Request $request)
    {
        if (isNewVersion()) {
            $controller = new \App\Http\Controllers\V9\ProductController();
            return $controller->search($request);
        }

        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        //不想看产品ids 用字符串拼接
        $data['blackIdsStr'] = $request->input('blackIdsStr', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');

        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['userVipType'] = UserVipFactory::checkIsVip($data);

        //用户连登天数
        $data['login_count'] = UserFactory::fetchUserUnlockNumById($data['userId']);
        $data['unlock_data'] = ProductStrategy::getUnlockProductIds($data);

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
        //点击过立即申请产品ids
//        $redisProIds = CacheFactory::fetchRedisProductIds($data['userId']);
        //筛选解锁产品id集合
        $productIds = ProductFactory::fetchNoDiffProductOrSearchIds($data);

        //对产品ids进行排序
        $data['productIds'] = [];
        if ($productIds) $productIds = ProductFactory::fetchSortProIds($data, $productIds);
//        if ($redisProIds) $redisProIds = ProductFactory::fetchSortProIds($data, $redisProIds);
        //redis中存在的产品ids放到产品列表最后面
//        $data['productIds'] = ProductStrategy::getProductIdsByRedisSort($productIds,$redisProIds);
        $data['productIds'] = $productIds;

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchVipProductsOrSearchs($data);
        $pageCount = $product['pageCount'];
        if (empty($product['list'])) {
            $data['pageSize'] = 1;
            $data['pageNum'] = 5;
            $product = ProductFactory::fetchLikeProductOrSearchs($data);
            $list_sign = 0;
            $pageCount = 1;
        }

        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $productVipIds;
        $counts = ProductFactory::fetchProductCounts($data);
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $productCommonIds;
        $commonCounts = ProductFactory::fetchProductCounts($data);

        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        //产品曝光统计事件
        $data['exposureProIds'] = implode(',', array_column($productLists, 'platform_product_id'));
        event(new DataProductEvent($data));

        //处理数据
        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['product_vip_count'] = $counts;
        $params['product_diff_count'] = intval($diffCounts);
        $params['list_sign'] = $list_sign;
        $params['bottom_des'] = ProductConstant::BOTTOM_DES;
        $params['is_vip'] = $data['userVipType'];

        return RestResponseFactory::ok($params);
    }
}