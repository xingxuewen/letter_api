<?php

namespace App\Http\Controllers\View;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\OneloanProductFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\OneloanProductStrategy;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;

/**
 * 一键贷产品
 *
 * Class OneloanProductController
 * @package App\Http\Controllers\View
 */
class OneloanProductController extends Controller
{
    /**
     * 一键贷匹配产品列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchResultProducts(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        //默认前3个
        $data['pageNum'] = $request->input('pageNum', 100);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';

        //贷款平台列表
        //根据手机号去log表中查推广类型
        $logTypeIds = UserSpreadFactory::fetchSpreadLogTypeIdByMobile($data);
        //根据手机号去batch表中查推广类型
        $batchTypeIds = UserSpreadFactory::fetchSpreadBatchTypeIdByMobile($data);
        $batchTypeIds = array_unique($batchTypeIds);
        $typeIds = array_merge($logTypeIds, $batchTypeIds);
        $res = [];
        if ($typeIds) {
            $res = SpreadStrategy::checkInsuranceResult($typeIds);
            //推广平台名称&logo
            $info = UserSpreadFactory::fetchSpreadTypeNameAndLogoByIds($res['typeIds']);
            $res['list'] = SpreadStrategy::getPartners($info);
            unset($res['typeIds']);
        }

        //产品列表
        $product = OneloanProductFactory::fetchSpreadProducts($data);
        //暂无数据
        if (empty($product['list'])) {
            return view('app.sudaizhijia.oneloan.product.result', ['data' => []]);
        }
        $pageCount = $product['pageCount'];
        //产品详情数据
        $products = OneloanProductFactory::fetchSpreadInfoProducts($product['list']);
        //产品列表数据处理
        $params['list'] = $products;
        $params['mobile'] = $data['mobile'];
        $products = OneloanProductStrategy::getSpreadProducts($params);
        //标签
        //$products = ProductFactory::tagsLimitOneToProducts($products);

        $lists['list'] = $products;
        $lists['pageCount'] = $pageCount;

        return view('app.sudaizhijia.oneloan.product.result', ['product' => $lists, 'result' => $res]);
    }

    public function fetchApplyView()
    {
        return view('app.sudaizhijia.oneloan.product.applyView');
    }
}
