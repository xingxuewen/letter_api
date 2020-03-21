<?php

namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserBankCardStrategy;
use Illuminate\Http\Request;

/**
 * 支付
 *
 * Class PaymentController
 * @package App\Http\Controllers\V1
 */
class PaymentController extends Controller
{
    /**
     * 订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrder(Request $request)
    {
        $order['pay_type'] = (int)$request->input('payType', 3);
        $order['terminal_id'] = $request->input('terminalId', '');
        $order['bankcard_id'] = (int)$request->input('bankcardId', 0); //user_banks表的ID
        $order['shadow_nid'] = $request->input('shadowNid', '');
        $order['user_id'] = (string)$request->user()->sd_user_id;
        $order['type'] = $request->input('type');
        $order['subType'] = $request->input('subType');
        //实名认证状态值
        $order['realnameType'] = $request->input('realnameType', '');

        $result = PaymentStrategy::getDiffOrderTypeChain($order);
        if (isset($result['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $result['error'], $result['code']);
        }

        return RestResponseFactory::ok($result);
    }

}