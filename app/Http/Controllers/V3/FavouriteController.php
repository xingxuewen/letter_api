<?php

namespace App\Http\Controllers\V3;

use App\Constants\UserVipConstant;
use App\Http\Controllers\Controller;
use App\Constants\ProductConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * 收藏模块
 *
 * Class FavouriteController
 * @package App\Http\Controllers\V3
 */
class FavouriteController extends Controller
{

    /**
     * 用户收藏 - 列表
     * 展示会员产品，饼模糊展示
     * n人今日申请
     * n位会员今日申请
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCollectionLists(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['mobile'] = $request->user()->mobile;

        //查询登录成功之后用户会员信息 需要判断是否需要模糊
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);

        //获取收藏的product_id
        $productIdArr = FavouriteFactory::fetchCollectionProductId($data['userId']);
        if (empty($productIdArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        //收藏产品列表
        $data['productIdArr'] = $productIdArr;
        $products = FavouriteFactory::fetchProductCollections($data);
        $pageCount = $products['pageCount'];
        if (empty($products['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $products = ProductFactory::tagsLimitOneToProducts($products['list']);
        //数据处理
        $data['list'] = $products;

        //vip用户可查看产品ids
        $data['vipProductIds'] = ProductFactory::fetchDivisionProductIds();
        //数据处理
        $products = ProductStrategy::getProductOrSearchLists($data);

        $res['list'] = $products;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }
}