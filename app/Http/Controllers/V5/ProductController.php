<?php

namespace App\Http\Controllers\V5;

use App\Constants\BannersConstant;
use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductEvent;
use App\Events\V1\DataProductExposureEvent;
use App\Events\V1\DataProductTagLogEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\CommentStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V4
 */
class ProductController extends Controller
{
    /**
     * 产品详情 - 产品特色
     * 相似产品推荐  千人千面
     * 根据标签匹配规则推荐产品
     * 没有产品默认展示top2
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailProductLike(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //最热评论
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 2);
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //定位设备号
        $data['deviceNum'] = $request->input('deviceId', '');
        //马甲标识
        $data['shadow_nid'] = $request->input('shadowNid', 'sudaizhijia');

        //标签筛选产品
        $data['matchIds'] = ProductFactory::fetchProductTagMatch($data['productId']);
        //区分会员、定位、不想看，获取最终展示产品ids
        $data['productIds'] = ProductFactory::fetchFilterProductIdsByConditions($data);
        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);


        //产品信息
        $product = ProductFactory::productOneFromProNothing($data['productId']);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //产品评论置顶消息
        $commentCounts = CommentFactory::commentCounts($data['productId']);
        //评论分类总数
        $params['score'] = number_format($product['satisfaction'], 1);
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

        //区分会员产品标志的产品id
        $data['vipProductIds'] = ProductFactory::fetchDivisionProductIds();

        $likeProduct = ProductFactory::fetchProductListsByTagMatchs($data);
        if (empty($likeProduct['list'])) {
            //查询推荐的top2
            //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
            $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
            //筛选产品id集合
            $data['productIds'] = ProductFactory::fetchProductOrSearchIds($data);
            $likeProduct = ProductFactory::fetchLikeProductOrSearchs($data);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['like_list'] = $productLists;

        //流水监听
        $data['list'] = $productLists;
        $data['from'] = ProductConstant::PRODUCT_TAG_RULE_DETAIL_FROM;
        event(new DataProductTagLogEvent($data));

        return RestResponseFactory::ok($params);
    }

    /**
     * 第五版 产品列表 & 速贷大全筛选
     * 所有产品都展示
     * 非登录，非会员：所有vip在下，一个特殊的vip产品可以随意排序位置
     * 会员登录：vip混排，只要符合相应的筛选规则即可
     * “n人今日申请”更换为“n位会员今日申请”
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

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
        //筛选产品id集合
        $data['productIds'] = ProductFactory::fetchNoDiffVipProductOrSearchIds($data);
        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);

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
     *  产品详情 - 计算器计算
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCalculators(Request $request)
    {
        //产品id
        $productId = $request->input('productId', '');
        //额度
        $info['loanMoney'] = $request->input('loanMoney', '');
        //期限
        $info['loanTimes'] = $request->input('loanTimes', '');
        //产品详情
        $info['info'] = ProductFactory::productOneFromProNothing($productId);
        if (empty($info['info'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //计算器所需计算数据
        $params = ProductStrategy::getCalculatorFormatData($info);
        //产品对应费率
        $fee = ProductFactory::fetchProductFee($productId);
        if (empty($fee)) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //根据利息金额转化
        $params = ProductStrategy::getFormatLoanTimesByInterest($params);
        //逾期费
        $params['fee'] = $fee;
        //将利率全部转化为利率费用
        $calcuCost['cost'] = ProductStrategy::getCalculatorInterestInfo($params);
        $calcuCost['overdue_alg'] = $params['overdue_alg'];
        $calcuCost['loanMoney'] = $params['loanMoney'];
        $calcuCost['loanTimes'] = $params['loanTimes'];
        $calcuCost['pay_method'] = $params['pay_method'];
        $calcuCost['interest_alg'] = $info['info']['interest_alg'];
        //数据格式处理，加和求总计
        $calcuTotal = ProductStrategy::getCalculatorTotalRes($calcuCost);
        //利息格式处理
        $calcuTotal['params'] = $params;
        $calcuTotal = ProductStrategy::getCalculatorInterests($calcuTotal);

        return RestResponseFactory::ok($calcuTotal);
    }

    /**
     * 产品详情 - 产品大数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailProductDatas(Request $request)
    {
        $data = $request->all();
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
        //认证用户真是姓名
        $user = UserIdentityFactory::fetchUserRealInfo($data['userId']);
        $data['realname'] = isset($user['name']) ? $user['name'] : '';
        //是否是vip产品
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);
        //整合数据
        $product = ProductStrategy::getDetailProductDatas($data);

        //判断是否收藏产品
        $product['sign'] = FavouriteFactory::collectionProducts($data['userId'], $data['productId']);

        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $products = ProductFactory::productOneFromProNothing($data['productId']);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($userId);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //访问产品详情记录流水表
        $productLog = ProductFactory::createProductLog($userId, $data, $user, $products, $deliverys);

        return RestResponseFactory::ok($product);
    }

    /**
     * 首页推荐产品
     * 新用户、连登用户 ∩ 热门推荐可见产品
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

        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //用户最大连登天数
        $userUnlo = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);
        $data['userUnloCount'] = $userUnlo ? $userUnlo['login_count'] : 0;
        //热门推荐第一版唯一标识
        $data['recommendSign'] = $request->input('recommendSign',
            BannersConstant::BANNER_UNLO_LOGIN_PRO_RECOMMEND_SIGN);

        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);
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
        $productIds = ProductFactory::fetchRecommendProductsByUserUnlocLogin($data);
        //热门推荐展示产品ids
        $recomProIds = $productIds['recomProIds'];
        //热门推荐+用户连登可见产品ids
        $listProIds = $productIds['listProIds'];
        //点击过立即申请产品ids
        $redisProIds = CacheFactory::fetchRedisProductIds($data['userId']);
        //对产品ids进行排序
        $data['productIds'] = [];
        //redis中存在的产品ids不展示
        $data['productIds'] = array_diff($listProIds, $redisProIds);
        //热门推荐产品列表
        $recommends = ProductFactory::fetchRecommendsByUserAndRedis($data);
        $pageCount = $recommends['pageCount'];
        //暂无数据
        if (empty($recommends['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $recommends['list'] = ProductFactory::tagsLimitOneToProducts($recommends['list']);

        //曝光统计事件监听
        $data['exposureProIds'] = implode(',', array_column($recommends['list'], 'platform_product_id'));
        event(new DataProductEvent($data));

        //数据处理
        $res['list'] = ProductStrategy::getProductOrSearchLists($recommends);
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 首页推荐产品
     * 新用户、连登用户 ∩ 热门推荐可见产品
     * 不做缓存，只展示课件的推荐产品
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

        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');
        //用户最大连登天数
        $userUnlo = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);
        $data['userUnloCount'] = $userUnlo ? $userUnlo['login_count'] : 0;
        //热门推荐第一版唯一标识
        $data['recommendSign'] = $request->input('recommendSign',
            BannersConstant::BANNER_UNLO_LOGIN_PRO_RECOMMEND_SIGN);

        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);
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
        $productIds = ProductFactory::fetchRecommendProductsByUserUnlocLogin($data);
        //热门推荐展示产品ids
        $recomProIds = $productIds['recomProIds'];
        //热门推荐+用户连登可见产品ids
        $listProIds = $productIds['listProIds'];
        //点击过立即申请产品ids
        $redisProIds = [];
        //对产品ids进行排序
        $data['productIds'] = [];
        //redis中存在的产品ids不展示
        $data['productIds'] = array_diff($listProIds, $redisProIds);
        //热门推荐产品列表
        $recommends = ProductFactory::fetchRecommendsByUserAndRedis($data);
        $pageCount = $recommends['pageCount'];
        //暂无数据
        if (empty($recommends['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $recommends['list'] = ProductFactory::tagsLimitOneToProducts($recommends['list']);

        //曝光统计事件监听
        $data['exposureProIds'] = implode(',', array_column($recommends['list'], 'platform_product_id'));
        event(new DataProductEvent($data));

        //数据处理
        $res['list'] = ProductStrategy::getProductOrSearchLists($recommends);
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }
}