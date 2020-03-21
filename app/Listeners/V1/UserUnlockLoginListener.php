<?php

namespace App\Listeners\V1;

use App\Events\V1\UserUnlockLoginEvent;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserOrderFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Constants\UserVipConstant;
use App\Helpers\Utils;
use App\Strategies\UserVipStrategy;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserVipSubtype;
use Illuminate\Support\Facades\DB;
use App\Redis\RedisClientFactory;

/**
 * 用户联登监听事件
 *
 * Class UserUnlockLoginListener
 * @package App\Listeners\V1
 */
class UserUnlockLoginListener extends AppListener
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }


    /**
     * @param UserUnlockLoginEvent $event
     */
    public function handle(UserUnlockLoginEvent $event)
    {
        $data = $event->data;

        //用户所有登录天数
        $loginDays = UserFactory::fetchUserUnlockLoginLogByUserId($data['userId']);

        //用户连续登录数据
        $userUnlockLogin = UserFactory::fetchUserUnlockLogin($loginDays);

        //用户连续登录统计详情
        $unlock_info = UserFactory::fetchUserUnlockLoginTotalByUserId($data['userId']);

        //修改用户联登解锁统计表
        $params = [];
        $params['userId'] = $data['userId'];
        $params['maxContinueLogins'] = $userUnlockLogin['max'];
        $params['nearContinueLogins'] = $userUnlockLogin['near'];
        $params['maxCount'] = count($loginDays);

        $unlock = UserFactory::updateUserUnlockLoginByUserId($params);
        if (!$unlock) {
            logError('用户联登统计失败-try', $params);
        }else{

            logInfo('login30_1',$params);

            //此时判断连登是不是30天，30天充一个月会员
            if($userUnlockLogin['near']!=0 && $userUnlockLogin['near']%30==0){

                try {
                        logInfo('login30_2',['userId'=>$data['userId'],'datetime'=>date('Y-m-d H:i:s')]);

                        //查询今天是否已经创建过送会员的订单
                        $is_have_order = UserOrderFactory::getOrderByPayType($data['userId'],6);

                        //如果当天没有创建订单
                        if (empty($is_have_order)) {

                            $redis = RedisClientFactory::get();

                            //查询redis今天是否记录了用户登录信息，如果没记录则记录
                            $key = 'login30_user_'.$data['userId'];

                            $is_have_order_today = $redis->getSet($key,'1');

                            if($is_have_order_today!=='1'){

                                logInfo('login30_3',['userId'=>$data['userId'],'datetime'=>date('Y-m-d H:i:s')]);

                                $redis->expireAt($key, strtotime(date('Y-m-d 23:59:59')));

                                $type_nid = UserVipConstant::VIP_MONTHLY_MEMBER;

                                //随机生成订单ID
                                $orderId = 'LOGIN-30-' . strtoupper(str_random(20));

                                logInfo('login30_4',['userId'=>$data['userId'],'data'=>$orderId]);

                                //订单类型
                                $order_type = UserOrderFactory::getOrderType($type_nid);

                                //会员子类型信息
                                $subtypeInfo = UserVipSubtype::where(['type_nid' => $type_nid, 'status' => 1])->first();

                                //价格
                                $price = 0;

                                //子类型ID
                                $subtypeId = 0;

                                if (!empty($subtypeInfo)) {
                                    $subtypeInfo = $subtypeInfo->toArray();
                                    //$price = $subtypeInfo['present_price'];
                                    $subtypeId = $subtypeInfo['id'];
                                }

                                //初始化订单
                                $orderInfo = [
                                    'user_id'           => $data['userId'],
                                    'orderid'           => $orderId,
                                    'payment_order_id'  => '',
                                    'order_type'        => $order_type,
                                    'pay_type'          => 6,
                                    'payment_type'      => 5,
                                    'terminaltype'      => $data['_os_type'],
                                    'terminalid'        => $data['_device_id'],
                                    'user_agent'        => $data['_user_agent'],
                                    'created_ip'        => Utils::ipAddress(),
                                    'created_at'        => date('Y-m-d H:i:s'),
                                    'request_text'      => '',
                                    'amount'            => $price,
                                    'subtype'           => $subtypeId,
                                    'status'            => 1
                                ];

                                DB::beginTransaction();

                                logInfo('login30_5',['userId'=>$data['userId'],'data'=>$orderInfo]);

                                $res = UserOrderFactory::createOrder($orderInfo);

                                if (!$res) {
                                    logError('order create fail', ['orderInfo' => $orderInfo]);
                                    throw new \Exception('order create fail');
                                }

                                $vipInfo = [
                                    'user_id'    => $data['userId'],
                                    'vip_type'   => 1,
                                    'subtype_id' => $subtypeId,
                                    'vip_no'     => UserVipStrategy::generateId(UserVipFactory::getVipLastId()),
                                ];

                                //充会员
                                $res = UserVipFactory::createVipInfoForUnlockLogin($vipInfo);

                                DB::commit();

                                if (!$res) {
                                    DB::rollBack();
                                    logError('user_vip create fail', ['vipInfo' => $vipInfo]);
                                    throw new \Exception('user_vip create fail');
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        logError('order create fail', ['error' => $e->getTraceAsString()]);
                    }
            }
        }
    }
}
