<?php

namespace App\Http\Controllers\Shadow\V2;

use App\Constants\ProductConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * 马甲产品
 * Class ProductController
 * @package App\Http\Controllers\Shadow\V2
 */
class ProductController extends Controller
{

    /**
     * 首页产品推荐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRecommendProducts(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //马甲唯一标识
        $data['shadow_nid'] = $request->input('shadowNid', 'shadow_jieqianbao');
        $shadowId = ProductFactory::fetchShadowIdByNid($data['shadow_nid']);
        //马甲产品ids
        $data['shadowProductIds'] = ProductFactory::fetchShadowProductIdsByLimit($shadowId);
        if (empty($data['shadowProductIds'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $product = ProductFactory::fetchProductsShadowByPosotion($data);
        $pageCount = 1;
        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;

        return RestResponseFactory::ok($params);

    }


    /**
     * 马甲 产品列表 & 速贷大全筛选
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProducts(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');
        //马甲id
        $data['shadowId'] = ShadowFactory::getShadowIdByNid($data['shadowNid']);

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchProductsShadow($data);
        $pageCount = $product['pageCount'];
        if (empty($product['list'])) {
            $data['productType'] = 1;
            $data['pageSize'] = 1;
            $data['pageNum'] = 5;
            $product = ProductFactory::fetchProductsShadow($data);
            $list_sign = 0;
            $pageCount = 1;
        }

        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['list_sign'] = $list_sign;

        return RestResponseFactory::ok($params);
    }


    /**
     * 产品详情 - 产品大数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailProductDatas(Request $request)
    {
        $data = $request->all();
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;

        //产品详情
        $data['info'] = ProductFactory::productOneFromProNothing($data['productId']);
        if (empty($data['info'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //下款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $loanSpeed = ProductPropertyFactory::fetchPropertyValue($data['productId'], $key);
        $data['loanSpeed'] = empty($loanSpeed) ? '3600' : $loanSpeed;
        //审批条件标签
        $approval_condition = ProductConstant::PRODUCT_DETAIL_APPROVAL_CONDITION;
        $condition['type_id'] = ProductFactory::fetchApprovalConditionTypeId($approval_condition);
        $condition['productId'] = $data['productId'];
        $data['condition_tags'] = ProductFactory::fetchDetailTags($condition);
        //信用贴士标签
        $credit_tips = ProductConstant::PRODUCT_DETAIL_CREDIT_TIPS;
        $tips['type_id'] = ProductFactory::fetchApprovalConditionTypeId($credit_tips);
        $tips['productId'] = $data['productId'];
        $data['tips_tags'] = ProductFactory::fetchDetailTags($tips);
        //手机号
        $data['mobile'] = UserFactory::fetchMobile($data['userId']);
        //整合数据
        $product = ProductStrategy::getDetailProductDatas($data);

        //判断是否收藏产品
        $product['sign'] = FavouriteFactory::collectionProducts($data['userId'], $data['productId']);

        //马甲产品排序
        $data['position'] = ProductFactory::fetchShadowProductPosition($data);
        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $products = ProductFactory::fetchProduct($data['productId']);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($userId);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //判断啊产品是都是vip产品
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);
        //访问产品详情记录流水表
        $productLog = ProductFactory::createShadowProductLog($userId, $data, $user, $products, $deliverys);

        return RestResponseFactory::ok($product);
    }

    /**
     * 产品详情 - 产品特色
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailProductLike(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');

        //马甲唯一标识
        $data['shadow_nid'] = $request->input('shadowNid', 'shadow_jieqian360');
        $shadowId = ProductFactory::fetchShadowIdByNid($data['shadow_nid']);
        //马甲产品ids
        $data['shadowProductIds'] = ProductFactory::fetchShadowProductIds($shadowId);

        //定位设备id
        $deviceId = $request->input('deviceId', '');
        //根据设备id获取城市id
        $data['deviceId'] = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['deviceId']);

        //产品信息
        $product = ProductFactory::productOneFromProNothing($data['productId']);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //推荐产品
        $likeProduct = ProductFactory::fetchShadowLikeProducts($data);
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        if (empty($productLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($productLists);
    }

}