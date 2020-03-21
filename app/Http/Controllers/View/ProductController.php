<?php

namespace App\Http\Controllers\View;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataBannerUnlockEvent;
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
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\CreditcardBannersStrategy;
use App\Strategies\CreditcardTypeStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * 产品相关view
 * Class ProductController
 * @package App\Http\Validators\View
 */
class ProductController extends Controller
{
    /**
     * 置顶分类专题h5页面
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchTopSpecials(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 100);
        $data['productType'] = isset($data['productType']) ? $data['productType'] : 1;
        $data['specialId'] = $request->input('specialId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //手机号
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //分类产品对应id
        $specialIds = ProductFactory::fetchSpecialId($data);
        $productIds = explode(',', $specialIds['product_list']);

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

        //分类产品
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        $data['productIds'] = $productIds;
        $data['specialLists'] = ProductFactory::fetchProductSpecials($data);
        //暂无数据
        if (empty($data['specialLists'])) {
            return view('app.sudaizhijia.errors.error_static');
        }

        $specialLists = ProductStrategy::getProductSpecials($data);
        $res['pageCount'] = $specialLists['pageCount'];
        $res['specialLists'] = $specialLists['list'];
        //处理数据
        $res['specialIds'] = $specialIds;
        $res['productType'] = $data['productType'];
        $res['mobile'] = $data['mobile'];
        $specialLists = ProductStrategy::getSpecialLists($res);
//        return RestResponseFactory::ok($specialLists);
        return view('app.sudaizhijia.product.specials.special_top', ['data' => $specialLists]);
    }

    /**
     * 异形广告
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
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
            return view('app.sudaizhijia.errors.error_static');
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
        return view('app.sudaizhijia.product.specials.shaped', ['data' => $params]);
    }


    /**
     * V2
     * 置顶分类专题h5页面
     *
     * 增加 专题描述\相似专题推荐
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchTopSpecialsSortV2(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 100);
        $data['productType'] = isset($data['productType']) ? $data['productType'] : 1;
        $data['specialId'] = $request->input('specialId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //手机号
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //分类产品对应id
        $specialIds = ProductFactory::fetchSpecialId($data);
        $productIds = explode(',', $specialIds['product_list']);

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

        //分类产品
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        $data['productIds'] = $productIds;
        $data['specialLists'] = ProductFactory::fetchProductSpecials($data);
        //暂无数据
        if (empty($data['specialLists'])) {
            return view('app.sudaizhijia.errors.error_static', ['error' => RestUtils::getErrorMessage(2106)]);
        }

        $specialLists = ProductStrategy::getProductSpecials($data);
        $res['pageCount'] = $specialLists['pageCount'];
        $res['specialLists'] = $specialLists['list'];

        //相似专题推荐
        $data['likeLimit'] = 3;
        $likeSpecials = CreditcardBannersFactory::fetchLikeSpecials($data);
        //数据处理
        $likeSpecials = CreditcardBannersStrategy::getLikeSpecials($likeSpecials);

        //处理数据
        $res['specialIds'] = $specialIds;
        $res['productType'] = $data['productType'];
        $res['mobile'] = $data['mobile'];
        $specialLists = CreditcardTypeStrategy::getSpecialLists($res);
//        return RestResponseFactory::ok($specialLists);

        return view(
            'app.sudaizhijia.product.specials_v2.special_top',
            [
                'lists' => $specialLists,
                'likeLists' => $likeSpecials,
            ]
        );
    }

    /**
     * V1
     * 分类专题h5页面
     *
     * 将原有原生页面转化为view页面
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchSpecials(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 100);
        $data['productType'] = isset($data['productType']) ? $data['productType'] : 1;
        $data['specialId'] = $request->input('specialId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //手机号
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //分类产品对应id
        $specialIds = ProductFactory::fetchSpecialId($data);
        $productIds = explode(',', $specialIds['product_list']);

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

        //分类产品
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        $data['productIds'] = $productIds;
        $data['specialLists'] = ProductFactory::fetchProductSpecials($data);
        //暂无数据
        if (empty($data['specialLists'])) {
            return view('app.sudaizhijia.errors.error_static', ['error' => RestUtils::getErrorMessage(2106)]);
        }

        $specialLists = ProductStrategy::getProductSpecials($data);
        $res['pageCount'] = $specialLists['pageCount'];
        $res['specialLists'] = $specialLists['list'];

        //相似专题推荐
        $data['likeLimit'] = 3;
        $likeSpecials = CreditcardBannersFactory::fetchLikeSpecials($data);
        //数据处理
        $likeSpecials = CreditcardBannersStrategy::getLikeSpecials($likeSpecials);

        //处理数据
        $res['specialIds'] = $specialIds;
        $res['productType'] = $data['productType'];
        $res['mobile'] = $data['mobile'];
        $specialLists = CreditcardTypeStrategy::getSpecialLists($res);
//        return RestResponseFactory::ok($specialLists);

        return view(
            'app.sudaizhijia.product.specials.special',
            [
                'lists' => $specialLists,
                'likeLists' => $likeSpecials,
            ]
        );
    }

    /**
     * 解锁连登产品列表
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

        //判断用户是否是会员
        $isVip = UserVipFactory::checkIsVip($data);
        //查询解锁连登产品
        $data['unlockProIds'] = ProductFactory::fetchProductUnlockLoginByLoginId($bannerId);
        $productDatas['list'] = [];
        $productDatas['pageCount'] = 0;
        if ($data['unlockProIds']) {
            //地域筛选+区分会员非会员
            $productIds = ProductFactory::fetchFiltersDisVipAndDevice($data);
            //连登可展示产品
            $productIds = array_intersect($productIds, $data['unlockProIds']);
            //点击过立即申请产品ids
            $redisProIds = CacheFactory::fetchRedisProductIds($data['userId']);
            //对产品ids进行排序
            $data['productIds'] = [];
            if ($productIds) {
                $productIds = ProductFactory::fetchProIdsByPosition($productIds);
            }
            if ($redisProIds) {
                $redisProIds = ProductFactory::fetchProIdsByPosition($redisProIds);
            }
            //redis中存在的产品ids放到产品列表最后面
            $data['productIds'] = ProductStrategy::getProductIdsByRedisSort($productIds, $redisProIds);
            //产品列表
            $products = ProductFactory::fetchUnlockLoginProducts($data);
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

        return view('app.sudaizhijia.product.unlock_login.unlock', ['products' => $productDatas, 'datas' => $datas]);
    }
}
