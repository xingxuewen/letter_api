<?php

namespace App\Http\Controllers\V1;

use App\Events\V1\UserIdfaEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\ComModelFactory;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\DataFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Idfa\IdfaService;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * Class DataController
 * @package App\Http\Controllers\V1
 * 数据统计
 */
class DataController extends Controller
{
    /**
     * post机申请记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPosLog(Request $request)
    {
        $params = $request->all();
        $result = DataFactory::insertPostLog($params);

        if (!$result) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2101), 2101);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * 统计活跃用户
     */
    public function updateActiveUser(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        //修改用户的访问时间visit_time
        $visitTime = UserFactory::updateActiveUser($userId);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * 产品申请点击流水统计
     */
    public function createProductApplyLog(Request $request)
    {
        $data = $request->all();
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //判断是否是vip产品
        $data['productId'] = $productId;
        $data['is_vip_product'] = ProductFactory::checkIsVipProduct($data);

        if (empty($userArr) || empty($productArr) || empty($deliveryArr) || empty($deliveryId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品申请点击流水统计
        DeliveryFactory::createProductApplyLog($userId, $userArr, $productArr, $deliveryArr, $data);
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return mixed
     * 宫格产品申请点击流水统计
     */
    public function createProductApplyGonggeLog(Request $request)
    {
        $productId = $request->input('productId');
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        if (empty($productArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //获取平台url
        $platformUrl = PlatformFactory::fetchPlatformUrl($productArr['platform_id']);

        $productArr['platform_url'] = $platformUrl;
        $productArr['user_agent'] = UserAgent::i()->getUserAgent();
        //宫格产品申请点击流水统计
        DeliveryFactory::createProductApplyGonggeLog($productArr);
        //单个平台点击立即申请数据统计
        PlatformFactory::updatePlatformClick($productArr['platform_id']);
        //单个产品点击立即申请数据统计
        ProductFactory::updateProductClick($productId);

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**
     * 投放统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserIdfa(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['idfaId'] = $request->input('idfaId', '');
        $data['appName'] = $request->input('appName', '');
        $data['source'] = $request->input('source', '');

        //根据投放标识、用户id为0查数据
        $idfaByIds = DataFactory::fetchUserIdfaByUserIdEmpty($data);
        //根据投放标识、用户id不为0查数据
        $idfaUser = DataFactory::fetchUserIdfaByUserId($data);

        if ($idfaByIds && !$idfaUser) {
            $res = DataFactory::createUserIdfa($data);
        } else {
            $res = DataFactory::updateUserIdfaByIds($data);
        }

        if (empty($res)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        event(new UserIdfaEvent($data));

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 一键选贷款点击统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDataSpreadConfig(Request $request)
    {
        $id = $request->input('id', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        //查询一键选贷款配置详情
        $data['config'] = UserSpreadFactory::fetchSpreadConfigInfoById($id);
        if ($data['config'])  //存在
        {
            //获取渠道id
            $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
            //获取渠道信息
            $data['delivery'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

            //创建流水
            $log = DataFactory::createDataSpreadConfigLog($data);
            //修改总计数
            $clickCount = DataFactory::updateSpreadConfigClickCount($id);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 广告流水点击统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDataBannerCreditCard(Request $request)
    {
        $data = $request->all();
        $id = $request->input('id', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //查询广告详情
        $data['card'] = BannersFactory::fetchBannerCreditCardInfoById($id);
        if ($data['card']) //存在
        {
            //获取渠道id
            $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
            //获取渠道信息
            $data['delivery'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

            //创建流水
            $log = DataFactory::createDataBannerCreditCardLog($data);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 区域点击统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserRegionLog(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['deviceId'] = $request->input('deviceId', '');
        $data['shadowNid'] = $request->input('shadowNid', 'sudaizhijia');

        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //区域点击流水统计
        $log = BannersFactory::createUserRegionLog($data, $deliverys);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * 首页访问量单独统计
     *
     * @param Request $request
     */
    public function createPageView(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr', 'channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 产品下载监测流水统计
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDataProductDownloadLog(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['deviceId'] = $request->input('deviceId', '');
        //马甲名
        $data['shadowNid'] = $request->input('shadowNid', 'sudaizhijia');
        //包名
        $data['appName'] = $request->input('appName', 'sudaizhijia');

        //获取用户信息
        $data['users'] = UserFactory::fetchUserNameAndMobile($data['userId']);
        //获取产品信息
        $data['products'] = ProductFactory::fetchProduct($data['productId']);
        //是否是vip产品
        $data['product_is_vip'] = ProductFactory::checkIsVipProduct($data);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
        //获取渠道信息
        $data['deliverys'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //数据存储
        $res = DataFactory::createDataProductDownloadLog($data);
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}