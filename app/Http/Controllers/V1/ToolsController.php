<?php

namespace App\Http\Controllers\V1;

use App\Constants\ToolsConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Apply\ToolsApply\DoToolsApplyHandler;
use App\Models\Factory\ToolsFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\ToolsStrategy;
use Illuminate\Http\Request;

/**
 * 工具集控制器
 *
 * Class ToolsController
 * @package App\Http\Controllers
 */
class ToolsController extends Controller
{
    /**
     * 工具集列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTools()
    {
        //类型nid
        $typeNid = ToolsConstant::TOOLS_TYPE_NID;
        //类型id
        $typeId = ToolsFactory::fetchToolsTypeIdByNid($typeNid);
        //工具集数据
        $tools = ToolsFactory::fetchToolsByTypeId($typeId);
        //暂无数据
        if (empty($tools)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $tools = ToolsStrategy::getTools($tools);

        return RestResponseFactory::ok($tools);
    }

    /**
     * 工具对接地址
     * 对接 获取对接地址
     * 没有对接 返回原地址
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchToolsUrl(Request $request)
    {
        //工具id
        $data['toolsId'] = $request->input('toolsId', '');
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //工具详情
        $toolsInfo = ToolsFactory::fetchToolsById($data);
        //暂无数据
        if (empty($toolsInfo)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //获取用户手机号
        $user = UserFactory::fetchUserById($data['userId']);
        //数据处理
        $data = ToolsStrategy::getOauthToolsDatas($data, $user, $toolsInfo);

        //获取地址流程  有对接走对接  没有对接正常返回地址
        $res = new DoToolsApplyHandler($data);
        $re = $res->handleRequest();

        if (isset($re['error']) && $re['error'] && $re['code'] == 401) //登录
        {
            return RestResponseFactory::unauthorized();
        }

        if (isset($re['error'])) //错误提示
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        //返回地址
        $urls['app_link'] = $re['app_link'];
        $urls['h5_link'] = $re['h5_link'];

        return RestResponseFactory::ok($urls);
    }
}