<?php

namespace App\Http\Controllers\V1;

use App\Constants\GuidesConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Guides\Partner\DoPromotionHandler;
use App\Models\Factory\OperateFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use Illuminate\Http\Request;

/**
 * 引导页
 * Class GuidesController
 * @package App\Http\Controllers\V1
 */
class GuidesController extends Controller
{
    /**
     * 引导页配置
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchGuidesConfig(){
//        logInfo('参数', ['data' => 'aaaa']);
        //类型nid
        $typeNid = GuidesConstant::GUIDES_NID;
        //类型id
        $config = OperateFactory::fetchGuidesConfigByNid($typeNid);
        $data['guide_status'] = $config['status'];

        return RestResponseFactory::ok($data);
    }

    /**
     * 拍拍贷推广
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function promotionsPartner(Request $request)
    {
        //基础信息数据
        $data = $request->all();
        //用户信息
        $data['userId'] = $request->user()->sd_user_id;
//        $data['mobile'] = $request->user()->mobile;
        $response = new DoPromotionHandler($data);
        $res = $response->handleRequest();
        //请刷新重试
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}