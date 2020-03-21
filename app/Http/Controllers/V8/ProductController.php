<?php

namespace App\Http\Controllers\V8;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Redis\RedisClientFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use DB;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V8
 *
 */
class ProductController extends Controller
{
    /**
     * 第八版 产品列表 & 速贷大全筛选
     * 已解锁产品都展示
     * 记录登录天数 解锁产品数
     * 后台默认排序
     * 点击产品置后
     * 曝光度统计
     * 根据请求次数，进行产品循环展示
     * 排序规则：结算正常产品，轮播产品，内部产品，会员产品，限量产品，立即申请产品
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
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $data['productType'] = $productType;
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        $data['loanNeed'] = $request->input('loanNeed', '');
        $data['loanHas'] = $request->input('loanHas', '');
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
        //用户所在渠道
        $data['delivery_id'] = empty($data['userId']) ? 0 : UserFactory::fetchDeliveryIdByUserId($data['userId']);
        //用户所在渠道状态标识
        $data['delivery_sign'] = 1;

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);

        //总产品列表ids
        $data['productIds'] = ProductFactory::fetchNoDiffProductOrSearchIds($data);
        //查询轮播时间段
        $data['circuDates'] = ProductFactory::fetchCirculateDateByDay();

        //结算正常产品ids [10000,19999]
        $data['valProIds'] = ProductFactory::fetchValueProductIdsByPosition($data);
        //结算产品进行排序
        $data['finalVal'] = ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['valProIds']);

        //点击过立即申请产品ids
        $data['redisProIds'] = CacheFactory::fetchRedisProductIds($data['userId']);
        //立即申请产品排序
        $data['finalApply'] = isset($data['redisProIds']) ? ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['redisProIds']) : [];

        //去除立即申请之后的结算产品
//        $data['finalVal'] = array_diff($data['finalVal'], $data['finalApply']) ?? $data['finalVal'];

        //redis实例化
        $redis = new RedisClientFactory();
        //根据判断用户所属类别，得出key
        $key = ProductStrategy::fetchRedisKeyByUserinfo($data);

        //筛选条件不存在
        $loanAmount = explode(',', $data['loanAmount']);
        $filters = array_sum($loanAmount) + $data['loanTerm'] + $data['loanNeed'] + $data['loanHas'];

        //在轮播时间范围内，reids产品滞后
        if ($data['circuDates'] && $data['pageSize'] == 1 && $data['productType'] == 1 && $filters == 0) {
            //用户请求，后端服务器计数，用取模的方式获取请求位置，前端截取1-3之间的数据
            //当处于轮播时间时且请求为第一页时，轮播，key+1
            $filedNum = $redis->hget(ProductConstant::PRODUCT_CIRCULATE_LISTS_NEW_KEY, $key) ?? 0;

            //针对用户存入最近一次的请求次数，用于分页请求
            $redis->hset($key, 'userId:' . $data['userId'], $filedNum);

            //对轮播产品轮播处理，轮播产品 = 推荐产品 + 解锁产品
            if ($data['finalVal']) {

                if (count($data['finalVal']) != 0) $remainder = $filedNum % count($data['finalVal']);
                else $remainder = 0;
                //取膜为0，表示已经轮播一遍
                $forwardProIds = array_slice($data['finalVal'], $remainder);
                $appendProIds = array_slice($data['finalVal'], 0, $remainder);
                $data['finalVal'] = array_merge($forwardProIds, $appendProIds);
            }

            $redis->hincrby(ProductConstant::PRODUCT_CIRCULATE_LISTS_NEW_KEY, $key, 1);

        } elseif ($data['circuDates'] && $data['productType'] == 1 && $filters == 0 && $data['pageSize'] > 1) {
            //当处于轮播时间时,该用户请求第二页时，取该用户最近一次点击值
            $filedNum = $redis->hget($key, 'userId:' . $data['userId']) ?? 0;

            //对轮播产品轮播处理，轮播产品 = 推荐产品 + 解锁产品
            if ($data['finalVal']) {
                if (count($data['finalVal']) != 0) $remainder = $filedNum % count($data['finalVal']);
                else $remainder = 0;

                $forwardProIds = array_slice($data['finalVal'], $remainder);
                $appendProIds = array_slice($data['finalVal'], 0, $remainder);
                $data['finalVal'] = array_merge($forwardProIds, $appendProIds);
            }

        } elseif (!$data['circuDates']) {
            //当不在轮播时间时，重置参数
            $redis->hset(ProductConstant::PRODUCT_CIRCULATE_LISTS_NEW_KEY, $key, 0);
            $redis->del([$key]);
        }

        //轮播产品排序
        //$data['finalCir'] = ProductFactory::fetchSortProductIds($data, $data['productIds'], $redisValProIds);

        //内部产品
        $data['innerProIds'] = ProductFactory::fetchInnerProductIds();
        //内部产品排序
        $data['finalInner'] = ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['innerProIds']);

        //去除立即申请之后的内部产品
//        $data['finalInner'] = array_diff($data['finalInner'], $data['finalApply']) ?? $data['finalInner'];

        //会员产品
        $data['vipProIds'] = ProductFactory::fetchVipProductIds();
        //会员产品排序
        $data['finalVip'] = ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['vipProIds']);

        //去除立即申请之后的会员产品
//        $data['finalVip'] = array_diff($data['finalVip'], $data['finalApply']) ?? $data['finalVip'];

        //限量产品ids
        $data['limitProIds'] = ProductFactory::fetchLimitProductIdsByIsDelete();
        //限量产品排序
        $data['finalLimit'] = isset($data['limitProIds']) ? ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['limitProIds']) : [];

        //去除立即申请之后的限量产品
//        $data['finalLimit'] = array_diff($data['finalLimit'], $data['finalApply']) ?? $data['finalLimit'];

        //最终产品ids拼接
        $finalProIds = ProductStrategy::getProIdsByInnerAndValAndVipAndCache($data);

        //去重
        $data['finalProIds'] = array_unique($finalProIds);

        //如果 productType 不是1，则拿所有的产品ids
        if ($data['productType'] != 1 || $filters != 0) {
            $data['finalProIds'] = ProductFactory::fetchSortProductIds($data, $data['productIds'], $data['productIds']);
        }

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchProductsOrSearchsByConditions($data);
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