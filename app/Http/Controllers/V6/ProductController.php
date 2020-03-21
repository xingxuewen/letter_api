<?php

namespace App\Http\Controllers\V6;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductEvent;
use App\Events\V1\DataProductTagLogEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DataProductExposureFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductUnlockLoginRelFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Redis\RedisClientFactory;
use App\Strategies\BannerStrategy;
use App\Strategies\CommentStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use Predis\Client;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V4
 */
class ProductController extends Controller
{
    /**
     * 第六版 产品列表 & 速贷大全筛选
     * 所有产品都展示
     * 非登录，非会员：所有vip在下，一个特殊的vip产品可以随意排序位置
     * 会员登录：vip混排，只要符合相应的筛选规则即可
     * “n人今日申请”更换为“n位会员今日申请”
     * 曝光度统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrSearchs(Request $request)
    {
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
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);
        $data['userVipType'] = $data['vip_sign'];
        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
        //筛选产品id集合
        $data['productIds'] = ProductFactory::fetchNoDiffVipProductOrSearchId($data);

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchNoDiffVipProductsOrSearchs($data);
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
        //处理数据
        //会员产品id作为key
        $vipCommonDiffIds = array_diff($productVipIds, $productCommonIds);
        $data['vipProductIds'] = isset($vipCommonDiffIds) ? array_flip($vipCommonDiffIds) : [];
        $productLists = ProductStrategy::getProductOrSearchLists($data);


        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        //曝光统计
        if (!empty($productLists)) {
            $exposureData['user_id'] = $data['userId'];
            $exposureData['device_id'] = $data['deviceNum'];
            $exposureData['product_list'] = implode(',', array_column($productLists, 'platform_product_id'));
            DataProductExposureFactory::AddExposure($exposureData);
        }

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['product_vip_count'] = $counts;
        $params['list_sign'] = $list_sign;
        $params['product_diff_count'] = intval($diffCounts);
        $params['bottom_des'] = ProductConstant::BOTTOM_DES;
        $params['is_vip'] = $data['vip_sign'];

        return RestResponseFactory::ok($params);
    }

    /**
     * 热门推荐
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPromotionsAboutUser(Request $request)
    {
        if (isNewVersion()) {
            $controller = new \App\Http\Controllers\V9\ProductController();
            return $controller->recommends($request);
        }

        $data = $request->all();

        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';


        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['userVipType'] = UserVipFactory::checkIsVip($data);
        //用户连登天数
        $data['login_count'] = UserFactory::fetchUserUnlockNumById($data['userId']);
        //用户所在渠道
        $data['delivery_id'] = empty($data['userId']) ? 0 : UserFactory::fetchDeliveryIdByUserId($data['userId']);
        //渠道开关标识
        $data['delivery_sign'] = 1;

//        热门推荐
//        $typeNid = ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE;
//            //热门推荐类型id
//            $types = ProductFactory::fetchProductRecommendTypeByNid($typeNid);
//            //热门推荐不想看产品
//            $recoms['typeId'] = $types['id'];
//            $data['num'] = isset($types['num']) ? $types['num'] : 0;
//        $recomProIds = ProductFactory::fetchRecommendIdsByTypeId($recoms);
//        $data['finalRecom'] = ProductFactory::fetchSortProductIds325($recomProIds);


        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);

        //2.连登产品
        $data['unlock_data'] = ProductStrategy::getUnlockProductIds325($data);
        $unlockLoginProIds = ProductUnlockLoginRelFactory::getUnlockProductIdsByUnlockLoginIds($data['unlock_data']);
        $data['finalUnlockLogin'] = ProductFactory::fetchSortProductIds325($unlockLoginProIds);

        //3.内部产品
        $innerProIds = ProductFactory::fetchInnerProductIds();
        //内部产品排序
        $data['finalInner'] = ProductFactory::fetchSortProductIds325($innerProIds);

        //4.会员产品
        $vipProIds = ProductFactory::fetchVipProductIds();
        //会员产品排序
        $data['finalVip'] = ProductFactory::fetchSortProductIds325($vipProIds);

        //5.限量产品ids
        $limitProIds = ProductFactory::fetchLimitProductIdsByIsDelete();
        //限量产品排序
        $data['finalLimit'] = isset($limitProIds) ? ProductFactory::fetchSortProductIds325($limitProIds) : [];

        //6.点击过立即申请产品ids
        $redisProIds = CacheFactory::fetchRedisProductIds($data['userId']);

        //1.热门推荐产品 = 所有产品 - 不展示的推荐产品
        //筛选条件 区分会员+地域
        $data['productIds'] = ProductFactory::fetchFiltersDisVipAndDevice($data);
        //热门推荐可见产品
        $typeNid = ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE;
        //热门推荐类型id
        $types = ProductFactory::fetchProductRecommendTypeByNid($typeNid);
        //热门推荐不想看产品
        $recoms['typeId'] = $types['id'];
        $data['num'] = isset($types['num']) ? $types['num'] : 0;
        $data['recomNoProIds'] = ProductFactory::fetchRecommendIdsByTypeId($recoms);
        //可展示产品ids
        $recomProIds = array_diff($data['productIds'], $data['recomNoProIds']);
        //热门推荐展示产品ids
        $recomProIds = array_diff(
            $recomProIds,
            $redisProIds,
            $data['delProIds'],
            $data['finalUnlockLogin'],
            $data['finalInner'],
            $data['finalVip'],
            $data['finalLimit']
        );

        $data['finalRecom'] = ProductFactory::fetchSortProductIds325($recomProIds);


        //过滤掉该用户点击过的产品,得到最后的列表产品id,及达到限量的产品
        $data['finalRecom'] = array_diff($data['finalRecom'], $data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit']);
        $data['finalUnlockLogin'] = array_diff($data['finalUnlockLogin'], $data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit'], $data['recomNoProIds']);
        $data['finalCircle'] = array_merge($data['finalRecom'], $data['finalUnlockLogin']);

        //过滤terminalType
        $circleProIdsByTerminalType = ProductFactory::fetchProductsOrSearchsByConditions325($data['finalCircle'], $data['terminalType'],$data['productIds']);
        $data['finalCircle'] = array_column($circleProIdsByTerminalType, 'platform_product_id');
//        if (!BannerStrategy::judgeIsBannerTime()) {
        $data['finalCircle'] = ProductFactory::fetchSortProductIds325($data['finalCircle']);
//        }

        //判断轮播产品是否大于推荐展示数量
        $isOverRecomNum = count($data['finalCircle']) > $data['num'];
        if ($isOverRecomNum) {
            $data['finalCircle'] = array_slice($data['finalCircle'], 0, $data['num']);
        }

        $data['finalInner'] = array_diff($data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit']);
        $data['finalVip'] = array_diff($data['finalVip'], $redisProIds, $data['delProIds'], $data['finalLimit']);
//        $data['finalLimit'] = array_diff($data['finalLimit'], $redisProIds, $data['delProIds']);
        $redis = new RedisClientFactory();
        $recommandField = ProductStrategy::fetchRecommandRedisKeyByUserinfo($data);

        if (BannerStrategy::judgeIsBannerTime() && $data['pageSize'] == 1) {
            //当处于轮播时间时且请求为第一页时，轮播，key+1
            $recommandFieldNum = $redis->hget(ProductConstant::PRODUCT_RECOMMAND_CIRCULATE_LIST_KEY, $recommandField) ?? 0;
            //针对用户存入最近一次的请求次数，用于分页请求
            $redis->hset($recommandField, 'userId:' . $data['userId'], $recommandFieldNum);

            //对轮播产品轮播处理，轮播产品 = 推荐产品 + 解锁产品
            if ($data['finalCircle']) {
                $remainder = $recommandFieldNum % count($data['finalCircle']);
                $forwardProIds = array_slice($data['finalCircle'], $remainder);
                $appendProIds = array_slice($data['finalCircle'], 0, $remainder);
                $data['finalCircle'] = array_merge($forwardProIds, $appendProIds);
            }

            $redis->hincrby(ProductConstant::PRODUCT_RECOMMAND_CIRCULATE_LIST_KEY, $recommandField, 1);
        } elseif (BannerStrategy::judgeIsBannerTime() && $data['pageSize'] > 1) {
            //当处于轮播时间时,该用户请求第二页时，取该用户最近一次点击值
            $recommandFieldNum = $redis->hget($recommandField, 'userId:' . $data['userId']) ?? 0;

            //对轮播产品轮播处理，轮播产品 = 推荐产品 + 解锁产品
            if ($data['finalCircle']) {
                $remainder = $recommandFieldNum % count($data['finalCircle']);
                $forwardProIds = array_slice($data['finalCircle'], $remainder);
                $appendProIds = array_slice($data['finalCircle'], 0, $remainder);
                $data['finalCircle'] = array_merge($forwardProIds, $appendProIds);
            }
        } else {
            //当不在轮播时间时，重置参数
            $redis->hset(ProductConstant::PRODUCT_RECOMMAND_CIRCULATE_LIST_KEY, $recommandField, 0);
            $redis->del([$recommandField]);
        }

        if ($isOverRecomNum) {
            $finalProIds = $data['finalCircle'];
        } elseif ($data['userVipType']) {
            $finalProIds = array_merge($data['finalCircle'], $data['finalInner'], $data['finalVip']);
        } else {
            $finalProIds = array_merge($data['finalCircle'], $data['finalInner']);
        }

        //根据推荐产品设置参数截取
        $finalProIds = array_slice($finalProIds, 0, $data['num']);

        //产品列表 产品详情按顺序组装 过滤设备产品
        $lists = ProductFactory::fetchProductsOrSearchsByConditions325($finalProIds, $data['terminalType']);
        $listsMap = array_column($lists, null, 'platform_product_id');
        $finalProIds = array_intersect($finalProIds, array_keys($listsMap));

        $finalLists = [];
        foreach ($finalProIds as $proId) {
            $finalLists[] = $listsMap[$proId];
        }
        //推荐列表数据处理
        $finalLists = ProductFactory::tagsLimitOneToProducts($finalLists);
        $finalLists = ProductStrategy::getProductOrSearchLists(['list' => $finalLists]);
        $pageCount = ceil(count($finalLists) / $data['pageNum']);
        $productInfos = array_slice($finalLists, ($data['pageSize'] - 1) * $data['pageNum'], $data['pageNum']);

        if (empty($productInfos)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //曝光统计事件监听
        $data['exposureProIds'] = implode(',', array_column($finalLists, 'platform_product_id'));
        event(new DataProductEvent($data));

        //数据处理
        $res['list'] = $productInfos;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 热门推荐
     * 跟用户无关
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPromotions(Request $request)
    {
        if (isNewVersion()) {
            $controller = new \App\Http\Controllers\V9\ProductController();
            return $controller->recommends($request);
        }

        $data = $request->all();

        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';


        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['userVipType'] = UserVipFactory::checkIsVip($data);
        //用户连登天数
        $data['login_count'] = UserFactory::fetchUserUnlockNumById($data['userId']);

        //用户所在渠道
        $data['delivery_id'] = empty($data['userId']) ? 0 : UserFactory::fetchDeliveryIdByUserId($data['userId']);
        //渠道开关标识
        $data['delivery_sign'] = 1;

//        热门推荐
//        $typeNid = ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE;
//            //热门推荐类型id
//            $types = ProductFactory::fetchProductRecommendTypeByNid($typeNid);
//            //热门推荐不想看产品
//            $recoms['typeId'] = $types['id'];
//            $data['num'] = isset($types['num']) ? $types['num'] : 0;
//        $recomProIds = ProductFactory::fetchRecommendIdsByTypeId($recoms);
//        $data['finalRecom'] = ProductFactory::fetchSortProductIds325($recomProIds);


        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);

        //2.连登产品
        $data['unlock_data'] = ProductStrategy::getUnlockProductIds325($data);
        $unlockLoginProIds = ProductUnlockLoginRelFactory::getUnlockProductIdsByUnlockLoginIds($data['unlock_data']);
        $data['finalUnlockLogin'] = ProductFactory::fetchSortProductIds325($unlockLoginProIds);

        //3.内部产品
        $innerProIds = ProductFactory::fetchInnerProductIds();
        //内部产品排序
        $data['finalInner'] = ProductFactory::fetchSortProductIds325($innerProIds);

        //4.会员产品
        $vipProIds = ProductFactory::fetchVipProductIds();
        //会员产品排序
        $data['finalVip'] = ProductFactory::fetchSortProductIds325($vipProIds);

        //5.限量产品ids
        $limitProIds = ProductFactory::fetchLimitProductIdsByIsDelete();
        //限量产品排序
        $data['finalLimit'] = isset($limitProIds) ? ProductFactory::fetchSortProductIds325($limitProIds) : [];

        //6.点击过立即申请产品ids //这里点击过的产品取[]
        $redisProIds = [];

        //1.热门推荐产品 = 所有产品 - 不展示的推荐产品
        //筛选条件 区分会员+地域
        $data['productIds'] = ProductFactory::fetchFiltersDisVipAndDevice($data);
        //热门推荐可见产品
        $typeNid = ProductConstant::PRODUCT_RECOMMEND_HOME_UPGRADE;
        //热门推荐类型id
        $types = ProductFactory::fetchProductRecommendTypeByNid($typeNid);
        //热门推荐不想看产品
        $recoms['typeId'] = $types['id'];
        $data['num'] = isset($types['num']) ? $types['num'] : 0;
        $data['recomNoProIds'] = ProductFactory::fetchRecommendIdsByTypeId($recoms);
        //可展示产品ids
        $recomProIds = array_diff($data['productIds'], $data['recomNoProIds']);
        //热门推荐展示产品ids
        $recomProIds = array_diff(
            $recomProIds,
            $redisProIds,
            $data['delProIds'],
            $data['finalUnlockLogin'],
            $data['finalInner'],
            $data['finalVip'],
            $data['finalLimit']
        );
        $data['finalRecom'] = ProductFactory::fetchSortProductIds325($recomProIds);

        //过滤掉该用户点击过的产品,得到最后的列表产品id
        $data['finalRecom'] = array_diff($data['finalRecom'], $data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit']);
        $data['finalUnlockLogin'] = array_diff($data['finalUnlockLogin'], $data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit'], $data['recomNoProIds']);
        $data['finalCircle'] = array_merge($data['finalRecom'], $data['finalUnlockLogin']);
        $data['finalCircle'] = ProductFactory::fetchSortProductIds325($data['finalCircle'],$data['productIds']);
        //判断轮播产品是否大于推荐展示数量
        $isOverRecomNum = count($data['finalCircle']) > $data['num'];
        if ($isOverRecomNum) {
            $data['finalCircle'] = array_slice($data['finalCircle'], 0, $data['num']);
        }

        $data['finalInner'] = array_diff($data['finalInner'], $redisProIds, $data['delProIds'], $data['finalLimit']);
        $data['finalVip'] = array_diff($data['finalVip'], $redisProIds, $data['delProIds'], $data['finalLimit']);
        $data['finalLimit'] = array_diff($data['finalLimit'], $redisProIds, $data['delProIds'], $data['finalLimit']);

        if ($isOverRecomNum) {
            $finalProIds = $data['finalCircle'];
        } elseif ($data['userVipType']) {
            $finalProIds = array_merge($data['finalCircle'], $data['finalInner'], $data['finalVip']);
        } else {
            $finalProIds = array_merge($data['finalCircle'], $data['finalInner']);
        }

        //根据推荐配置展示数量截取
        $finalProIds = array_slice($finalProIds, 0, $data['num']);

        //产品列表 产品详情按顺序组装 过滤设备产品
        $lists = ProductFactory::fetchProductsOrSearchsByConditions325($finalProIds, $data['terminalType']);

        $listsMap = array_column($lists, null, 'platform_product_id');
        $finalProIds = array_intersect($finalProIds, array_keys($listsMap));

        $finalLists = [];
        foreach ($finalProIds as $proId) {
            $finalLists[] = $listsMap[$proId];
        }
        //推荐列表数据处理
        $finalLists = ProductFactory::tagsLimitOneToProducts($finalLists);
        $finalLists = ProductStrategy::getProductOrSearchLists(['list' => $finalLists]);
        //根据推荐产品配置截取num个产品
        $finalLists = array_slice($finalLists, 0, $types['num']);
        $pageCount = ceil(count($finalLists) / $data['pageNum']);
        $productInfos = array_slice($finalLists, ($data['pageSize'] - 1) * $data['pageNum'], $data['pageNum']);

        if (empty($productInfos)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //曝光统计事件监听
        $data['exposureProIds'] = implode(',', array_column($finalLists, 'platform_product_id'));
        event(new DataProductEvent($data));

        //数据处理
        $res['list'] = $productInfos;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }
}
