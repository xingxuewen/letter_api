<?php
/**
 * Created by PhpStorm.
 * User: zengqiang
 * Date: 17-10-27
 * Time: 上午9:57
 */

namespace App\Models\Factory;

use App\Constants\PaymentConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\AccountPayment;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserOrderType;


class UserOrderFactory extends AbsModelFactory
{
    /**
     * 创建订单
     *
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function createOrder($data)
    {
        return UserOrder::updateOrCreate($data);
    }

    /**
     * 获取支付渠道ID
     *
     * @param string $nid
     * @return int
     */
    public static function getPaymentType($nid = UserVipConstant::PAYMENT_TYPE)
    {
        $id = AccountPayment::where(['nid' => $nid,'status'=>"1"])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取订单类型ID
     *
     * @param string $nid
     * @return int
     */
    public static function getOrderType($nid = UserVipConstant::ORDER_TYPE)
    {
        $id = UserOrderType::where(['type_nid' => $nid])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 根据支付类型查询用户当天订单
     *
     * @param string $nid
     * @return int
     */
    public static function getOrderByPayType($userId,$payType)
    {
        $data = UserOrder::where(['user_id'=>$userId,'pay_type'=>$payType])
                         ->where('created_at', '>=', date('Y-m-d 00:00:00'))
                         ->first();

        return $data ? $data->toArray() : [];
    }

    public static function create(array $data)
    {

    }
}
