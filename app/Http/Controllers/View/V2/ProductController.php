<?php

namespace App\Http\Controllers\View\V2;

use App\Constants\BannersConstant;
use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataBannerUnlockEvent;
use App\Events\V1\DataProductEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\CreditcardBannersFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Redis\RedisClientFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\BannerStrategy;
use App\Strategies\CreditcardBannersStrategy;
use App\Strategies\CreditcardTypeStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use Laravel\Lumen\Http\ResponseFactory;
use Predis\Client;

/**
 * 产品相关view
 * Class ProductController
 * @package App\Http\Validators\View
 */
class ProductController extends Controller
{
    /**
     * V2
     * 异形banner产品列表
     *
     * 增加 专题描述
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchShapeds(Request $request)
    {
        //推荐产品标识
        $data['typeNid'] = $request->input('typeNid', ProductConstant::PRODUCT_SPECIAL_BANNER);
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 100);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //区分会员、地域最终筛选产品ids
        $data['productIds'] = ProductFactory::fetchVipDeviceProIds($data);

        //首页推荐产品
        $typeNid = $data['typeNid'];
        //类型表id
        $data['typeId'] = ProductFactory::fetchPlatformProductRecommendTypeIdByNid($typeNid);
        //关联产品ids
        $data['recommendProductIds'] = ProductFactory::fetchRecommendIdsByTypeId($data);
        //暂无数据
        if (empty($data['typeId']) || empty($data['recommendProductIds'])) {
            return view('app.sudaizhijia.errors.error_static', ['error' => RestUtils::getErrorMessage(2106)]);
        }

        //筛选产品
        $product = ProductFactory::fetchRecommendProducts($data);
        $pageCount = $product['pageCount'];
        //暂无产品数据
        if (empty($product['list'])) {
            return view('app.sudaizhijia.errors.error_static');
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        //处理数据
        //会员产品id作为key
        $vipCommonDiffIds = array_diff($productVipIds, $productCommonIds);
        $data['vipProductIds'] = isset($vipCommonDiffIds) ? array_flip($vipCommonDiffIds) : [];
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;

//        return RestResponseFactory::ok($params);
        return view('app.sudaizhijia.product.specials_v2.shaped', ['lists' => $params]);
    }

    /**
     * V2 解锁连登产品列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchUnlockLoginProducts(Request $request)
    {
        $data = $request->all();
        $bannerId = $request->input('unlockLoginId');
        $data['unlockLoginId'] = $bannerId;
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 100);
        $data['deviceNum'] = $request->input('deviceId', '');
        $data['shadowNid'] = $request->input('shadowNid', 'sudaizhijia');
        $data['clickSource'] = $request->input('clickSource', '');
        $data['terminalType'] = $request->input('terminalType', '');
//        $data['banner_unlock_type_id'] = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE_325);
        $data['banner_unlock_type_id'] = BannersFactory::fetchUnlockLoginTypeIdByNid(BannersConstant::BANNER_UNLOCK_LOGIN_TYPE);

        //用户所在渠道
        $data['delivery_id'] = empty($data['userId']) ? 0 : UserFactory::fetchDeliveryIdByUserId($data['userId']);
        //渠道开关标识
        $data['delivery_sign'] = 1;

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
        

        //判断用户是否是会员
        $isVip = UserVipFactory::checkIsVip($data);
        //查询解锁连登产品
        //获取解锁连登分组产品id
        $unlockLoginProductIdsGroup = BannerStrategy::getUnlockLoginProductIdsGroup($data);
        $data['unlockProIds'] = array_column($unlockLoginProductIdsGroup[$mapUnlockLoginId[$bannerId]] ?? [], 'product_id') ?? [];
        $productDatas['list'] = [];
        $productDatas['pageCount'] = 0;
        $isDelete2ProIds = [];

        if ($data['unlockProIds']) {
            //地域筛选+区分会员非会员
            $productIds = ProductFactory::fetchFiltersDisVipAndDevice($data);
            //连登可展示产品
            $productIds = array_unique(array_intersect($productIds, $data['unlockProIds']));
            //对当前所有产品id分组，结算/内部/限量
            $productIdsByType = BannerStrategy::dealProductIdsGroupByProductType($productIds);
            //点击过立即申请产品ids
            $redisProIds = CacheFactory::fetchRedisProductIds($data['userId']);
            //将productIdsByType内部各单元再细分为未点击/点击过
            $proIdsGroupByClick = BannerStrategy::dealProductIdsTypeGroupByClick($productIdsByType, $redisProIds);
            //对处理好的产品分组排序
            $proIdsGroupByClick = BannerStrategy::fetchProIdsByPosition($proIdsGroupByClick);
            //已达限量产品 按钮 申请已满
            $isDelete2ProIds = array_merge($proIdsGroupByClick['xianliang']['unclick'], $proIdsGroupByClick['xianliang']['clicked']);
//            dd($proIdsGroupByClick);
            //对产品ids进行轮播顺序处理
            $data['productIds'] = [];
//            dd($proIdsGroupByClick);
            //redis中存在的产品ids放到产品列表最后面
            $redis = new RedisClientFactory();

            foreach ($proIdsGroupByClick as $key => &$item) {
                //判断是否处于轮播时间
                if (!(BannerStrategy::judgeIsBannerTime())) {
                    //不处于轮播时间，则正常排序
                    $item = array_merge($item['unclick'], $item['clicked']);
//                    $redis->hset('banner_circle', 'top_pro_id_index', $thisTopProIdIndex);
                    //当不在轮播时间范围时，清楚redis缓存
                    $redis->hdel('banner_circle', ['top_pro_id_index']);
                } else {
                    //获取点击次数
                    $lastTopProIdIndex = $redis->hget('banner_circle', 'top_pro_id_index') ?? 0;

                    //结算产品轮播, 内部/限量不参与轮播
                    if ($key == 'jiesuan') {
                        //先轮播处理，再将点击过的产品交集放在后
                        //轮播处理
                        //1.先对结算产品排序
                        $productIdsByType['jiesuan'] = ProductFactory::fetchProIdsByPosition($productIdsByType['jiesuan']);
                        $remainder = 0;

                        if (count($productIdsByType['jiesuan'])) {
                            $remainder = $lastTopProIdIndex % count($productIdsByType['jiesuan']);
                        }

                        $forwardProIds = array_slice($productIdsByType['jiesuan'], $remainder);
                        $appendProIds = array_slice($productIdsByType['jiesuan'], 0, $remainder);
                        $circleProIds = array_merge($forwardProIds, $appendProIds);

                        $redis->hset('banner_circle', 'top_pro_id_index', $lastTopProIdIndex + 1);
                        //点击过的产品交集放在后
                        $forwardUnclickProIds = array_diff($circleProIds, $redisProIds);
                        $item = array_merge($forwardUnclickProIds, $item['clicked']);
                    } else {
                        $item = array_merge($item['unclick'], $item['clicked']);
                    }


////                    $redis->hset();
//                    $lastFirstProId = $redis->hget('banner_circle', 'unlock_one') ?? 0;
//                    //当处于轮播产品id查询不到时，默认-1
//                    $itemKey = array_search($lastFirstProId, $item['unclick']) ?: -1;
//                    //对未点击的产品进行轮播处理,对未点击产品轮播处理，点击过产品排后
//                    $item = array_merge(array_merge(array_slice($item['unclick'], $itemKey + 1), array_slice($item['unclick'], 0, $itemKey + 1)), $item['clicked'])  ;
                }
            }
            unset($item);
            $unlockLoginSortRule = BannersConstant::BANNER_UNLOCK_LOGIN_SORT;
            $finalProductIds = [];

            foreach ($unlockLoginSortRule as $rule) {
                $finalProductIds = array_merge($finalProductIds, $proIdsGroupByClick[$rule] ?? []);
            }
            //产品列表

            $products = ProductFactory::fetchUnlockLoginProductsV2($data, $finalProductIds);
            $pageCount = $products['pageCount'];
            if ($products) {
                //标签
                $data['list'] = ProductFactory::tagsLimitOneToProducts($products['list']);
                //数据处理
                $products = ProductStrategy::getProductOrSearchLists($data);
            }

            $productDatas['list'] = $products ? $products : [];
            $productDatas['pageCount'] = $pageCount;
        }

        if (empty($productDatas['list'])) {
            return view('app.sudaizhijia.errors.error_static', ['error' => '抱歉，暂无符合您的产品哦~']);
        }

        //根据position展示，position=3，最后一个按钮不展示

        //判断是需要弹窗，还是调起下一个连登页面
        //查询当前解锁连登广告信息
        $bannerUnlock = BannersFactory::fetchBannerUnlockLoginById($bannerId);
        //下一期解锁连登广告位信息
        $data['type_id'] = isset($bannerUnlock['type_id']) ? $bannerUnlock['type_id'] : 0;
        $data['position'] = isset($bannerUnlock['position']) ? $bannerUnlock['position'] + 1 : 0;
        $nexBannerUnlo = BannersFactory::fetchBannerUnlockLoginByPosition($data);
        //用户最大连登天数
        $userLogin = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);
        $login_count = isset($userLogin['login_count']) ? $userLogin['login_count'] : 0;
        $need_login_count = isset($nexBannerUnlo['unlock_day']) ? $nexBannerUnlo['unlock_day'] : 0;

        //是否展示下一期页面
        if ($isVip) {
            //会员用户不进行判断
            $datas['is_show_page'] = 1;
        } else {
            $datas['is_show_page'] = $login_count >= $need_login_count ? 1 : 0;
        }
        //本期主键id
        $datas['id'] = isset($bannerUnlock['id']) ? $bannerUnlock['id'] : 0;
        //下一期主键id
        $datas['unlockLoginId'] = isset($nexBannerUnlo['id']) ? $nexBannerUnlo['id'] : 0;
        //本期背景图片
        $datas['cover_img'] = QiniuService::getImgs($bannerUnlock['cover_img']);
        //本期背景色
        $datas['bg_color'] = $bannerUnlock['bg_color'];
        //位置
        $datas['position'] = $bannerUnlock['position'];
        $datas['title'] = $bannerUnlock['unlock_title'];
//        return RestResponseFactory::ok($datas);

        //流水统计事件
        event(new DataBannerUnlockEvent($data));
        logInfo('连登', ['products' => $productDatas, 'isDelete2ProIds' => $isDelete2ProIds]);

        //曝光统计事件监听
        if (!empty($productDatas['list'])) {
            $deviceId = $request->input('deviceId', '');
            $deviceId = $request->input('_device_id', $deviceId);
            //曝光统计事件监听
            $dataProductEvent = ['userId'=> $data['userId'],'deviceNum'=>$deviceId];
            $dataProductEvent['exposureProIds'] = implode(',', array_column($productDatas['list'], 'platform_product_id'));
            event(new DataProductEvent($dataProductEvent));
        }

        return view('app.sudaizhijia.product.unlock_login.unlockV2', [
            'products' => $productDatas,
            'datas' => $datas,
            'isDelete2ProIds' => $isDelete2ProIds,
        ]);
    }
}
