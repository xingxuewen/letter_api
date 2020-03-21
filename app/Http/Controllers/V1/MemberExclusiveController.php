<?php

namespace App\Http\Controllers\V1;


use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductSearchFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

class MemberExclusiveController extends Controller
{
    /**
     * 会员独家产品列表 & 速贷大全筛选
     * 所有会员产品都展示
     * 非登录，非会员：所有vip，一个特殊的vip产品可以随意排序位置
     * 会员登录：vip混排，只要符合相应的筛选规则即可
     * “n人今日申请”更换为“n位会员今日申请”
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUserVipProductList(Request $request)
    {
        $data = $request->all();
//        logInfo('jieshou数据', ['ids' =>$data]);
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
        //筛选会员产品id集合
        $data['productIds'] = ProductFactory::fetchNoDiffVipExclusiveProductOrSearchIds($data);
//        logInfo('筛选会员产品id集合', ['ids' =>$data['productIds']]);
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
//        logInfo('产品列表最终', $data['list']);
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
//        logInfo('产品id', $data['vipProductIds']);
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['product_vip_count'] = $diffCounts;
        $params['list_sign'] = $list_sign;
        $params['product_diff_count'] = intval($diffCounts);
        $params['bottom_des'] = ProductConstant::BOTTOM_DES;
        $params['is_vip'] = $data['vip_sign'];

        return RestResponseFactory::ok($params);
    }

    /**
     * 会员产品搜索热词列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userVipSearchHot(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //会员类型id
        $data['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        //会员可以看的产品
        $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        //非会员产品
        $ordinary['userNoVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
        $productNoVipIds = ProductFactory::fetchProductNoVipIdsByVipTypeId($ordinary);
        //会员产品
        $productVipIds = array_diff($productVipIds, $productNoVipIds);
        $data['productVipIds'] = $productVipIds;

        //限制个数
        $data['limit'] = 16;
        $hots = ProductSearchFactory::fetchHotsAboutVipExclusive($data);
        //暂无数据
        if (empty($hots)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($hots);
    }

    /**
     * 会员独家
     * 搜索结果列表 与会员有关
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userVipSearchResult(Request $request)
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

        //查询登录成功之后用户会员信息
        $datas['vip_sign'] = UserVipFactory::checkIsVip($data);

        //会员可查看的产品
        $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_DEFAULT);
        $productVipIds = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        //非会员产品
        $ordinary['userNoVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
        $productNoVipIds = ProductFactory::fetchProductNoVipIdsByVipTypeId($ordinary);
        //会员产品
        $productVipIds = array_diff($productVipIds, $productNoVipIds);
        $data['productVipIds'] = $productVipIds;
        $datas['vipProductIds'] = isset($productVipIds) ? array_flip($productVipIds) : [];

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

        //处理数据
        $lists['list'] = ProductStrategy::getProductOrSearchLists($datas);
        $lists['pageCount'] = $pageCount;

        return RestResponseFactory::ok($lists);

    }
}