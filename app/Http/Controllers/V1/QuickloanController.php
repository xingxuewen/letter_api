<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Quickloan\Quickloan\DoQuickloanHandler;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\QuickloanFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Strategies\UserIdentityStrategy;
use Illuminate\Http\Request;

/**
 * 极速贷
 *
 * Class QuickloanController
 * @package App\Http\Controllers\V1
 */
class QuickloanController extends Controller
{

    /**
     * 极速贷
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchQuickloanUrl(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['configId'] = $request->input('configId', '');

        //极速贷配置详情
        $data['config'] = QuickloanFactory::fetchQuickloanConfigInfoById($data['configId']);
        if (empty($data['config'])) //无显示数据
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $res = new DoQuickloanHandler($data);
        $re = $res->handleRequest();

        if (isset($re['error']) && $re['error'] && $re['code'] == 401) //登录
        {
            return RestResponseFactory::unauthorized();
        }

        if (isset($re['error'])) //错误提示
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        $spreads['url'] = $re['url'];

        return RestResponseFactory::ok($spreads);
    }
}