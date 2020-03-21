<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Constants\ProductConstant;
use App\Events\Shadow\ShadowProductApplyEvent;
use App\Events\V1\DataProductTagLogEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;
use App\Models\Factory\ProductFactory;
use App\Strategies\ProductStrategy;

/**
 * 产品模块
 *
 * Class ProductController
 * @package App\Http\Controllers\Shadow\V1
 */
class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * 产品详情——点击借款
     */
    public function apply(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');

        // 获取平台网址
        $url = PlatformFactory::fetchShadowProductUrl($data);
        if (empty($url)) {
            $url = PlatformFactory::fetchProductUrl($data);
        }

        // 获取产品信息
        $product = ProductFactory::fetchProduct($data['productId']);
        // 获取用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        // 获取shadow id
        $shadowId = ShadowFactory::getShadowIdByNid($data['shadowNid']);
        $data['shadowId'] = $shadowId;
        // 获取shadow nid
        $shadowNid = ShadowFactory::getShadowNid($shadowId);
        // 获取渠道id sd_user_shadow
        $deliveryId = ShadowFactory::getDeliveryId($data);
        // 获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        if (empty($user) || empty($product) || empty($shadowId) || empty($deliveryArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //产品位置
        if (isset($data['shadowNid']) && !empty($data['shadowNid']) && $data['shadowNid'] != 'sudaizhijia') {

            $position = ProductFactory::fetchShadowProductPosition($data);
        } else {
            $position = isset($product['position_sort']) ? $product['position_sort'] : '99';
        }

        //判断啊产品是都是vip产品
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);

        //数据处理
        $data = [
            'user_id' => $userId,
            'username' => $user['username'],
            'mobile' => $user['mobile'],
            'platform_id' => $data['platformId'],
            'platform_product_id' => $data['productId'],
            'platform_product_name' => $product['platform_product_name'],
            'product_is_vip' => isset($data['is_vip_product']) ? $data['is_vip_product'] : '99',
            'channel_id' => $deliveryArr['id'],
            'channel_title' => $deliveryArr['title'],
            'channel_nid' => $deliveryArr['nid'],
            'shadow_nid' => empty($shadowNid) ? 'sudaizhijia' : $shadowNid,
            'position' => $position,
            'click_source' => isset($data['clickSource']) ? $data['clickSource'] : '',
            'user_agent' => UserAgent::i()->getUserAgent(),
            'create_at' => date('Y-m-d H:i:s', time()),
            'create_ip' => Utils::ipAddress(),
        ];

        // 立即申请触发事件记录流水
        event(new ShadowProductApplyEvent($data));

        return RestResponseFactory::ok(['url' => $url]);
    }

    /** 马甲速贷大全列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrSearchs(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['shadowNid'] = $request->input('shadowNid', 'shadow_jieqian360');
        //马甲id
        $data['shadowId'] = ShadowFactory::getShadowIdByNid($data['shadowNid']);

        //产品列表
        $product = ProductFactory::fetchProductsShadow($data);

        //产品查看类型 0全部,1:iOS,2:Android,3:WEB
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;

        $pageCount = $product['pageCount'];

        //标签
        $productLists = ProductFactory::tagsByAll($product['list']);
        //暂无产品
        if (empty($productLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $productLists = ProductStrategy::getProductsOrSearchs($productType, $productLists, $pageCount);

        return RestResponseFactory::ok($productLists);

    }

    /**
     * 借钱360第二版 产品列表 & 速贷大全筛选
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrFilters(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '0,');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '0,');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

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
     * 第二部分 产品详情 展示评论与同类产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailOther(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        //最热评论
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 2);

        //产品信息
        $product = ProductFactory::productOne($data['productId']);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品评论置顶消息
        $commentCounts = CommentFactory::commentCounts($data['productId']);
        //评论分类总数
        $params['score'] = $product['satisfaction'] . '';
        $params['counts'] = CommentStrategy::getCommentCounts($commentCounts);
        //置顶评论ids
        $commentTopIds = CommentFactory::fetchCommentTopIds($data['productId']);
        //没有数据
        if (empty($commentTopIds)) {
            $params['comment_list'] = [];
        } else {
            $data['commentIds'] = $commentTopIds;
            //所有评论 分页显示
            $comments = CommentFactory::fetchDetailCommentsById($data);
            //整理数据
            $commentDatas = CommentStrategy::getDetailComments($comments['list']);
            $params['comment_list'] = $commentDatas;
        }

        //推荐产品
        $data['productType'] = 1;
        $data['pageSize'] = 1;
        $data['pageNum'] = 2;
        $likeProduct = ProductFactory::fetchProductsShadow($data);
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['like_list'] = $productLists;

        return RestResponseFactory::ok($params);
    }


    /**
     * 标签匹配推荐产品列表
     * 点击立即申请:有标签规则匹配相似产品，推荐权重前三个
     *      否则 推荐默认top3
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductTagMatchs(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 3);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //马甲标识
        $data['shadow_nid'] = $request->input('shadowNid', 'sudaizhijia');

        //标签筛选产品
        $data['matchIds'] = ProductFactory::fetchProductTagMatch($data['productId']);
        //区分会员、定位、不想看，获取最终展示产品ids
        $data['productIds'] = ProductFactory::fetchFilterProductIdsByConditions($data);

        $product = ProductFactory::fetchProductListsByTagMatchs($data);
        $pageCount = 1;

        //暂无产品数据
        if (empty($product['list'])) {
            //查询推荐的top3
            //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
            $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
            //筛选产品id集合
            $data['productIds'] = ProductFactory::fetchProductOrSearchIds($data);
            $product = ProductFactory::fetchLikeProductOrSearchs($data);
            $pageCount = 1;

        }

        //无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //标签
        $data['vipProductIds'] = ProductFactory::fetchDivisionProductIds();
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);
        //区分会员产品标志的产品id
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        //流水统计
        $data['list'] = $productLists;
        $data['from'] = ProductConstant::PRODUCT_TAG_RULE_QUALIFY_FROM;
        event(new DataProductTagLogEvent($data));

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;

        return RestResponseFactory::ok($params);
    }
}