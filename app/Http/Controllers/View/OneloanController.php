<?php

namespace App\Http\Controllers\View;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Http\Controllers\Controller;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;

/**
 * 速贷之家 - 一键选贷款
 * Class OneloanController
 * @package APP\Http\Controllers\View
 */
class OneloanController extends Controller
{
    /**
     * 基础信息页面
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchBasic(Request $request)
    {
        //用户信息
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['sign'] = isset($request->user()->accessToken) ? $request->user()->accessToken : '';
        //logInfo('basic', ['data' => $data]);
//        $data['userId'] = 123763;
//        $data['mobile'] = '13522960570';

        //查询用户填写基础信息
        $basicInfo = UserSpreadFactory::fetchBasicInfo($data);
        //logInfo('basic赋值', ['data' => $basicInfo]);

        return view('app.sudaizhijia.oneloan.basic', ['data' => $basicInfo]);
    }

    /**
     * 完整信息
     * 返回html代码
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchFull(Request $request)
    {
        //用户信息
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //logInfo('full', ['data' => $data]);
//        $data['userId'] = 123763;
//        $data['mobile'] = '13522960570';

        //查询用户填写基础信息
        $fullInfo = UserSpreadFactory::fetchSpreadInfo($data);
        //用户填写信息进度
        $progress = SpreadStrategy::getSpreadInfoProgress($fullInfo);
        //logInfo('fullData',['data'=>$progress,'fullInfo'=>$fullInfo]);

        $viewObj = view('app.sudaizhijia.oneloan.full', ['data' => $fullInfo, 'progress' => $progress]);
        $htmlStr = response($viewObj)->getContent();

        return $htmlStr;
    }

    /**
     * 结果页
     * 返回html代码
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fetchResult(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        //默认前3个
        $data['pageNum'] = $request->input('pageNum', 3);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        //不想看产品ids 用字符串拼接
        $blackIdsStr = $request->input('blackIdsStr', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $deviceId = $request->input('deviceId', '');
        //logInfo('result接收值',['data'=>$request->all()]);

//        $data['userId'] = 123763;
//        $data['mobile'] = '13522960570';

        //根据手机号去log表中查推广类型
        $logTypeIds = UserSpreadFactory::fetchSpreadLogTypeIdByMobile($data);
        //根据手机号去batch表中查推广类型
        $batchTypeIds = UserSpreadFactory::fetchSpreadBatchTypeIdByMobile($data);
        $batchTypeIds = array_unique($batchTypeIds);
        $typeIds = array_merge($logTypeIds, $batchTypeIds);
        //logInfo('结果log&batch的ids',['data'=>$typeIds]);
        $res = [];
        if ($typeIds) {
            $res = SpreadStrategy::checkInsuranceResult($typeIds);
            //推广平台名称&logo
            $info = UserSpreadFactory::fetchSpreadTypeNameAndLogoByIds($res['typeIds']);
            $res['list'] = SpreadStrategy::getPartners($info);
            unset($res['typeIds']);
        }
        //logInfo('result', ['data' => $res]);


        //根据设备id获取城市id
        $data['deviceId'] = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['deviceId']);

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

        //不想看产品ids
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $data['blackIds'] = array_diff($blackIds, $blackIdsStr);

        //产品列表
        $product = ProductFactory::fetchProductsOrFilters($data);
        $pageCount = 0;
        //暂无产品数据
        $productLists = [];
        if ($product['list']) {
            //标签
            $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);
            $productLists = ProductStrategy::getProductOrSearchLists($data);
        }

        $productData['list'] = $productLists;
        $productData['pageCount'] = $pageCount;
        //logInfo('推荐产品结果',['list'=>$productData]);

        $viewObj = view('app.sudaizhijia.oneloan.result', ['result' => $res, 'product' => $productData]);
        $htmlStr = response($viewObj)->getContent();

        return $htmlStr;
    }


    /**
     * 协议页面
     * 返回html代码
     */
    public function fetchAgreement()
    {
        $viewObj = view('app.sudaizhijia.oneloan.agreement');
        $htmlStr = response($viewObj)->getContent();
        return $htmlStr;
    }

    /**
     * 定位城市页面
     * 返回html代码
     */
    public function fetchCitys()
    {
        $viewObj = view('app.sudaizhijia.oneloan.citys');
        $htmlStr = response($viewObj)->getContent();
        return $htmlStr;
    }
}
