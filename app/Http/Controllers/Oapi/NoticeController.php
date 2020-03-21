<?php
namespace App\Http\Controllers\Oapi;


use App\Constants\LieXiongConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\Utils;
use App\Models\Factory\PaymentFactory;
use App\Models\Orm\UserAuth;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserVip;
use App\Services\LieXiong\LieXiong;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class NoticeController extends Controller
{


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function liexiong(Request $request)
    {
        $params = $request->post();

        //$params = json_decode('{"cardId":"5cee02475e956600077941d5","userCardId":"5cf0f3eb039849000698ebee","orderId":"5cf48e88a8d52b000637fde6","type":"OK","price":"6900","time":"1559531178840","phone":"13717609999","userId":"123584","attach":"","sign":"Zbu1LgXSiW4/lySdZYG02sgCR5kzAzOPxVeOWQk1Ydo="}', true);

        $obj = new LieXiong();

        if (!$obj->validSign($params)) {
            logError('valid sign error');
            return RestResponseFactory::unauthorized("valid sign error", 1000);
        }

        if (empty($params['orderId'])) {
            logError('order id error');
            return RestResponseFactory::unauthorized("order id error", 1141);
        }

        $res = null;

        switch ($params['type']) {
            case 'OK': // 购买成功
                $res = $this->_liexiongPay($params);
                break;

            case 'REFUND':     // 全额退款
            case 'PART_REFUND':  // 部分退款

                logError('user refund', ['type' => $params['type']]);
                break;

            case 'REFUND_CARD':  // 全额退款,退卡
            case 'PART_REFUND_REFUND_CARD': // 部分退款，退卡
                $res = $this->_liexiongRefund($params);
                break;

            case 'CLOSE':  // 取消
            default:
                $res = RestResponseFactory::unauthorized('type error', 1000);
                break;
        }

        return $res;
    }

    /**
     * 烈熊支付回调
     * @param $params
     */
    protected function _liexiongPay($params)
    {
        $orderId = $params['orderId'];
        $orderInfo = UserOrder::where(['payment_order_id' => $orderId, 'payment_type' => LieXiongConstant::PAYMENT_TYPE])->first();
        logInfo('order info', ['payment_order_id' => $orderId, 'payment_type' => LieXiongConstant::PAYMENT_TYPE]);
        if (!empty($orderInfo) && $orderInfo->status == 1) {
            logInfo('order paid');
            return RestResponseFactory::ok();
        }

        try {
            if (empty($orderInfo)) {
                throw new \Exception('order info error');
            }

            if ($orderInfo->status != 0) {
                throw new \Exception('order status error');
            }

            if (bccomp($orderInfo->amount, bcdiv($params['price'], 100, 2), 2) !== 0) {
                throw new \Exception('order price error');
            }

            DB::beginTransaction();

            $orderInfo->status = 1;
            $orderInfo->updated_at = date('Y-m-d H:i:s', time());
            $orderInfo->updated_ip = Utils::ipAddress();

            // 支付金额
            //$orderInfo->paid = bcdiv($params['price'], 100, 2);

            $res = $orderInfo->save();
            //var_dump($orderInfo->user_id);exit;
            if (!$res) {
                throw new \Exception('db user_order modify fail');
            }

            $userInfo = UserAuth::select('mobile')->where('sd_user_id', '=', $orderInfo->user_id)->first();
            if (empty($userInfo)) {
                throw new \Exception('db user_auth select fail');
            }

            $service = new LieXiong();
            $cardInfo = $service->userVipCards($params['userCardId'], $userInfo->mobile, $orderInfo->user_id);

            if (empty($cardInfo)) {
                throw new \Exception('fetch userVipCards info fail');
            }

            $endTime = empty($cardInfo['endDate']) ? '' : date('Y-m-d H:i:s', strtotime($cardInfo['endDate']));

            $params = [
                'end_time' => $endTime,
                'vip_type' => 1,
            ];

            $res = PaymentFactory::updateUserVipSubStatus($orderInfo->user_id, 1, $orderInfo->subtype, $params);

            if (!$res) {
                throw new \Exception('db user_vip update fail');
            }

            DB::commit();
            return RestResponseFactory::ok();
        } catch (\Exception $e) {
            logError('order pay error', ['error' => $e->getTraceAsString()]);
            DB::rollBack();
            return RestResponseFactory::unauthorized("payment type error", 1141);
        }
    }

    /**
     * 烈熊退款回调
     *
     * @param $params
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _liexiongRefund($params)
    {
        $orderId = $params['orderId'];

        //$orderInfo = UserOrder::where(['orderid' => $orderId])->first();
        $orderInfo = UserOrder::where(['payment_order_id' => $orderId, 'payment_type' => LieXiongConstant::PAYMENT_TYPE])->first();

        if (empty($orderInfo)) {
            logInfo('order id error');
            return RestResponseFactory::unauthorized("order id error", 1141);
        }

        // 是否 烈熊 订单
        if (intval($orderInfo->payment_type) != LieXiongConstant::PAYMENT_TYPE) {
            logInfo('payment type error');
            return RestResponseFactory::unauthorized("payment type error", 1141);
        }

        // 订单状态不是已支付
        if (intval($orderInfo->status) != 1) {
            logInfo('order status error');
            return RestResponseFactory::unauthorized("order status error", 1141);
        }

        try {
            DB::beginTransaction();

            /*
            $res = UserOrder::where(['orderid' => $orderId, 'payment_type' => 4])->update([
                'payment_order_id' => $params['orderId'],
                //'amount' => bcdiv($params['amount'], 100, 2),
                'status' => 6,  // 已退款
                'response_text' => json_encode($params),
                'updated_at' => date('Y-m-d H:i:s', time()),
            ]);
            */

            $orderInfo->status = 6;
            $orderInfo->response_text = json_encode($params);
            $orderInfo->updated_at = date('Y-m-d H:i:s', time());

            if (!$orderInfo->save()) {
                throw new \Exception('db user_order modify fail');
            }

            $userVipInfo = UserVip::where(['user_id' => $orderInfo->user_id])->first();

            if (!empty($userVipInfo)) {
                $userVipInfo->status = 0;
                $userVipInfo->updated_at = date('Y-m-d H:i:s', time());
                $userVipInfo->updated_ip = Utils::ipAddress();
                if (!$userVipInfo->save()) {
                    throw new \Exception('db user_vip modify fail');
                }
            }

            DB::commit();

            return RestResponseFactory::ok();
        } catch (\Exception $e) {
            logError('order refund error', ['error' => $e->getMessage()]);
            DB::rollBack();
            return RestResponseFactory::unauthorized("payment type error", 1141);
        }
    }
}
