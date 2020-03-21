<?php

namespace App\Models\Factory;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\AccountPayment;
use App\Models\Orm\AccountPaymentConfig;
use App\Models\Orm\AccountPaymentType;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserOrderType;
use App\Models\Orm\UserRealName;
use App\Models\Orm\UserVip;
use App\Models\Orm\UserVipSubtype;
use App\Models\Orm\UserVipType;
use App\Strategies\UserVipStrategy;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentFactory
 * @package App\Models\Factory
 * 支付工厂
 */
class PaymentFactory extends AbsModelFactory
{
    public static $errorNoMap = [
        'CP000001' => '操作次数过多，请更换银行卡~',
        'CP000002' => '交易金额有误，请核查~',
        'CP000003' => '交易金额有误，请核查~',
        'CP000004' => '交易金额有误，请核查~',
        'CP000005' => '交易金额有误，请核查~',
        'CP000006' => '交易金额有误，请核查~',
        'CP000007' => '交易金额超出无卡交易金额限额，请联系发卡行~',
        'CP000008' => '交易异常，请稍后再试~',
        'CP000009' => '不支持当前交易，请联系发卡行~',
        'CP000010' => '不支持当前交易，请联系发卡行~',
        'CP000011' => '交易存在风险，请联系发卡行',
        'CP000012' => '不支持当前交易，请联系发卡行~',
        'CP000013' => '不支持当前交易，请联系发卡行~',
        'CP000014' => '当前交易账户没有该币种，请联系发卡行~',
        'CP000015' => '持卡人自主关闭该业务，请开通后再试~',
        'CP000016' => '交易金额超限，详请联系发卡行~',
        'CP000017' => '交易金额超限，请先修改单笔消费限额额度~',
        'CP000018' => '当前银行卡不支持此类交易，请更换银行卡~',
        'CP000019' => '无此发卡行，请更换银行卡~',
        'CP000020' => '操作异常，请重新再试~',
        'CP000021' => '操作异常，请稍后再试~',
        'CP000023' => '操作异常，请稍后再试~',
        'CP000024' => '交易金额有误，请核查~',
        'CP000025' => '超出消费次数限制，请联系发卡行~',
        'CP000026' => '操作异常，请稍后再试~',
        'CP000027' => '交易异常，请更换银行卡~',
        'CP000028' => '发卡行操作异常，请联系发卡行~',
        'CP000030' => '交易失败，详情请咨询中国银联 95516~',
        'CP000031' => '操作异常，请稍后再试~',
        'CP000032' => '发卡行应答失败，请联系发卡行~',
        'CP000033' => '持卡人账户不支持协议支付，请联系签约行~',
        'CP000034' => '持卡人账户不支持协议支付，请联系签约行~',
        'CP000035' => '转账货币不一致，请更换银行卡~',
        'CP000037' => '交易异常，请联系发卡行~',
        'CP000038' => '交易异常，请联系发卡行~',
        'CP000039' => '交易异常，请联系发卡行~',
        'CP000040' => '交易异常，请联系发卡行~',
        'CP000041' => '当前卡不支持快捷支付，请更换银行卡~',
        'CP000042' => '交易异常，请联系发卡行~',
        'CP000044' => '支付失败，请稍后再试~',
        'CP000099' => '交易失败，请联系发卡行~',
        'CP100001' => '银行卡已注销，请联系发卡行~',
        'CP100002' => '银行卡已冻结，请联系发卡行~',
        'CP100003' => '银行卡超过有效期，请联系发卡行~',
        'CP100004' => '银行卡已锁定，请联系发卡行~',
        'CP100005' => '银行卡未激活，请联系发卡行~',
        'CP100006' => '银行卡异常，请联系发卡行~',
        'CP100007' => '银行卡余额不足~',
        'CP100008' => '银行卡不支持快捷',
        'CP100009' => '当前卡不支持此交易，请更换银行卡~',
        'CP100010' => '账户状态异常，请联系发卡行~',
        'CP100011' => '银行卡状态异常，请联系发卡行~',
        'CP100012' => '支付失败，请联系我们~',
        'CP100013' => '密码输入次数超限~',
        'CP100014' => '支付失败，请稍后再试~',
        'CP100015' => '支付失败，请稍后再试~',
        'CP100016' => '当前卡不支持快捷支付，请更换银行卡~',
        'CP100017' => '支付失败，请稍后再试~',
        'CP100018' => '支付失败，请稍后再试~',
        'CP100019' => '支付失败，请稍后再试~',
        'CP100020' => '当前卡不支持快捷支付，请更换银行卡~',
        'CP100021' => '当前卡交易次数过多，请更换银行卡~',
        'CP100022' => '当前卡交易次数过多，请更换银行卡~',
        'CP100023' => '单笔交易金额超过银行限制，请联系发卡行~',
        'CP100024' => '当日交易金额超过银行限制，请联系发卡行~',
        'CP100025' => '当月交易金额超过银行限制，请联系发卡行~',
        'CP100026' => '当日交易过于频繁，请明日再试~',
        'CP100027' => '交易金额超限，可更换银行卡或联系发卡行~',
        'CP100028' => '当前卡为没收卡，请更换银行卡~',
        'CP100029' => '当前卡为挂失卡，请更换银行卡~',
        'CP100030' => '当前卡为被窃卡，请更换银行卡~',
        'CP100031' => '当前卡为过期卡，请更换银行卡~',
        'CP100032' => '当前卡为受限制的卡，请更换银行卡~',
        'CP100033' => '当前卡为无效卡，请更换银行卡~',
        'CP100034' => '当前卡为无效卡，请更换银行卡~',
        'CP100035' => '当前卡为无效卡，请更换银行卡~',
        'CP100036' => '当前卡为无效卡，请更换银行卡~',
        'CP100037' => '当前卡为无效卡，请更换银行卡~',
        'CP100038' => '当前卡为无效卡，请更换银行卡~',
        'CP100039' => '当前卡为无效卡，请更换银行卡~',
        'CP100040' => '当前卡为无效卡，请更换银行卡~',
        'CP100041' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100042' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100043' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100044' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100045' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100046' => '当前银行卡未激活，可更换银行卡或联系发卡行~',
        'CP100047' => '银行卡余额不足，请更换银行卡~',
        'CP100048' => '身份认证失败，请填写真实用户信息~',
        'CP100049' => '验证次数超限，请更换银行卡~',
        'CP100099' => '当前卡不支持快捷支付，请更换银行卡~',
        'CP110001' => '身份认证失败，请填写真实用户信息~',
        'CP110002' => '签约行查无此账号，请更换银行卡~',
        'CP110003' => '账户类型与签约行记录不符，请更换银行卡或联系签约行~',
        'CP110004' => '账户不支持协议支付，请更换银行卡或联系发卡行~~',
        'CP110005' => '账户已注销，更换银行卡或联系发卡行~',
        'CP110006' => '账户冻结中，更换银行卡或联系发卡行~',
        'CP110007' => '账户超过有效期，更换银行卡或联系发卡行~',
        'CP110008' => '账户已锁定，更换银行卡或联系发卡行~',
        'CP110009' => '账户未激活，更换银行卡或联系发卡行~',
        'CP110010' => '账户挂失，请更换银行卡或联系发卡行~',
        'CP110011' => '账户异常，请更换银行卡或联系发卡行~',
        'CP110012' => '当前手机机号与银行预留手机号不符，请保持一致~',
        'CP110013' => '持卡人账户未登记预留手机，请更换银行卡或联系发卡行~',
        'CP110014' => '持卡人未在签约行开通短信功能，请更换银行卡或联系发卡行~',
        'CP110015' => '"签约行的身份验证授权失败',
        'CP110016' => '"持卡人账户名称与签约行记录不符',
        'CP110017' => '持卡人账户名称格式有误，请更换银行卡或联系发卡行~',
        'CP110018' => '持卡人证件号与签约行记录不符，更换银行卡或联系发卡行~~',
        'CP110019' => '持卡人证件号格式有误，请更换银行卡或联系发卡行~',
        'CP110020' => '持卡人证件类型与签约行记录不符~',
        'CP110021' => '不支持此类证件验证，请联系签约行~',
        'CP110022' => '持卡人年龄未达要求，请联系签约行~',
        'CP110023' => '签约次数过多，更换银行卡或联系发卡行~',
        'CP110024' => '签约次数过多，更换银行卡或联系发卡行~',
        'CP110025' => '签约失败次数过多，请更换银行卡或联系发卡行~',
        'CP110026' => '短信验证码错误，请重新获取~',
        'CP110027' => '短信验证码已超时，请重新获取~',
        'CP110088' => '账户状态异常，请更换银行卡或联系发卡行~',
        'CP110099' => '签约失败，请更换银行卡或联系发卡行~',
        'CP120001' => '查无此签约协议号，请更换银行卡或联系发卡行~',
        'CP120002' => '当前卡不支持快捷支付，请更换银行卡再试~',
        'CP120088' => '当前卡不支持快捷支付，请更换银行卡再试~',
        'CP120099' => '当前卡不支持快捷支付，请更换银行卡再试~',
        'CP300001' => '交易异常，请稍后再试~',
        'CP300002' => '交易异常，请稍后再试~',
        'CP300003' => '交易异常，请稍后再试~',
        'CP300004' => '操作频繁，请更换银行卡再试~',
        'CP300099' => '系统网络超时，请更换银行卡再试~',
        'CS000001' => '系统网络超时，请更换银行卡再试~',
        'CS000002' => '系统网络超时，请更换银行卡再试~',
        'CS000003' => '系统网络超时，请更换银行卡再试~',
        'CS000004' => '系统网络超时，请更换银行卡再试~',
        'CS000005' => '系统网络超时，请更换银行卡再试~',
        'CS000006' => '银行连接超时，请更换银行卡再试~',
        'CS000007' => '银行响应超时，请更换银行卡再试~',
        'JP000099' => '当前卡不支持快捷支付，请更换银行卡~',
        'JS000099' => '当前卡不支持快捷支付，请更换银行卡~',
        '10080000' => '系统异常，请稍后再试~',
        '10080002' => '系统异常，请稍后再试~',
        '10080003' => '系统异常，请稍后再试~',
        '10080004' => '系统异常，请稍后再试~',
        '10080005' => '系统异常，请稍后再试~',
        '10080006' => '系统异常，请稍后再试~',
        '10080007' => '系统异常，请稍后再试~',
        '10080013' => '系统异常，请稍后再试~',
        '10080014' => '系统异常，请稍后再试~',
        '10080015' => '系统异常，请稍后再试~',
        '10080016' => '系统异常，请稍后再试~',
        '10080017' => '系统异常，请稍后再试~',
        '10080018' => '系统异常，请稍后再试~',
        '10080056' => '系统异常，请稍后再试~',
        '10080058' => '系统异常，请稍后再试~',
        '10080059' => '系统异常，请稍后再试~',
        '10080060' => '系统异常，请稍后再试~',
        '10080061' => '系统异常，请稍后再试~',
        '10080065' => '系统异常，请稍后再试~',
        '10080066' => '系统异常，请稍后再试~',
        '10080072' => '系统异常，请稍后再试~',
        '10090002' => '系统异常，请稍后再试~',
        '10090012' => '系统异常，请稍后再试~',
        '10090018' => '系统异常，请稍后再试~',
        '10090019' => '系统异常，请稍后再试~',
        '10090044' => '系统异常，请稍后再试~',
        '20090001' => '系统异常，请稍后再试~',
        '20090002' => '系统异常，请稍后再试~',
        '20090003' => '系统异常，请稍后再试~',
        '20090004' => '系统异常，请稍后再试~',
        '20070021' => '系统异常，请稍后再试~',
        '20070036' => '系统异常，请稍后再试~',
        '20070048' => '系统异常，请稍后再试~',
        '50010001' => '系统异常，请稍后再试~',
        '60010008' => '系统异常，请稍后再试~',
    ];

    public static function getErrorMsg($errorNo, $default = '')
    {
        return isset(self::$errorNoMap[$errorNo]) ? self::$errorNoMap[$errorNo] : $default;
    }

    /**
     * 获取订单状态
     *
     * @param $orderId
     * @return mixed
     */
    public static function getOrderStatus($orderId)
    {
        return UserOrder::where(['orderid' => $orderId])->value('status');
    }

    public static function getOrderStatus_wechat($orderId)
    {
        $message = UserOrder::select(['subtype', 'status'])->where(['orderid' => $orderId])->first();
        return $message ? $message->toArray() : [];
    }

    public static function getOrderStatus_new($orderId)
    {
        logInfo("00000000000000000000");
        $message = UserOrder::select(['subtype', 'status', 'card_num', 'order_type'])->where(['orderid' => $orderId])->first();
        logInfo("455555555555555555555555555");
        //  return UserOrder::where(['orderid' => $orderId])->value('status');
        return $message ? $message->toArray() : [];
    }

    // 获取订单状态--购买信用报告
    // by xuyj v3.2.3
    public static function getOrderStatus_report($id)
    {
        logInfo("00000000000000000000");
        $message = UserOrderType::select(['type_nid'])->where(['id' => $id])->first();
        logInfo("455555555555555555555555555");
        //  return UserOrder::where(['orderid' => $orderId])->value('status');
        return $message ? $message->toArray() : [];
    }


    /**
     * 支付渠道ID
     *
     * @param string $shadowNid
     * @return mixed
     */
    public static function getPaymentConfig($data = [])
    {
        $config = AccountPaymentConfig::where([
            'shadow_nid' => $data['shadowNid'],
            'payment_type' => $data['payType'],
            'status' => 1,
        ])
            ->orderBy('id', 'desc')->first();

        return $config ? $config->pay_id : 1;
    }

    /**
     * 获取渠道nid
     *
     * @param $paymentId
     * @return mixed
     */
    public static function getPaymentNid($paymentId)
    {
        return AccountPayment::where('id', $paymentId)->value('nid');
    }

    /**
     * 易宝公钥
     * 渠道公钥
     *
     * @return mixed
     */
    public static function getYibaoPublicKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('channel_public_key');
    }

    /**
     * 易宝商户私钥
     * 商户私钥
     *
     * @return mixed
     */
    public static function getYibaoMerchantPrivateKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_private_key');
    }

    /**
     * 易宝商户公钥
     * 商户公钥
     *
     * @return mixed
     */
    public static function getYibaoMerchantPublicKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_public_key');
    }

    /**
     * 获取易宝商户编号
     *
     * @return mixed
     */
    public static function getYibaoMerchantCode($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_code');
    }

    /**
     * 获取vip期限
     *
     * @return mixed
     */
    public static function getVipTime()
    {
        $value = UserVipType::where('type_nid', UserVipConstant::VIP_TYPE_NID)->value('vip_period');
        return isset($value) ? $value : 0;
    }


    /**
     * 更新VIP表
     *
     * @param $uid
     * @param $status
     * @param $vipType
     * @return bool
     */
    public static function updateUserVIPStatus($uid, $status, $vipType)
    {
        $typeId = UserVipFactory::getReVipTypeId($vipType);
        $price = UserVipFactory::getReVipAmount($vipType);
        $message = UserVip::select()->where(['user_id' => $uid, 'vip_type' => $typeId])->first();
        //根据不同类型处理不同的数据
        $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);
        $time = isset($data['time']) ? $data['time'] : date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
        $res = UserVip::where(['user_id' => $uid])->update([
            'vip_type' => $typeId,
            'status' => $status,
            'open_time' => date('Y-m-d H:i:s', time()),
            'start_time' => date('Y-m-d H:i:s', time()),
            'end_time' => $time,//date('Y-m-d H:i:s', UserVipStrategy::getVipExpired()),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
            'total_consuming' => DB::raw("total_consuming + " . $price),//isset($message['total_consuming']) ? ($message['total_consuming'] + $price) : $price,
            'total_count' => DB::raw("total_count + 1"),//isset($message['total_count']) ? ($message['total_count'] + 1) : 1,
        ]);

        return ($res > 0) ? true : false;
    }

    /**
     * 更新VIP表
     *
     * @param $uid
     * @param $status
     * @param $vipType
     * @return bool
     */
    public static function updateUserVipSubStatus($uid, $status, $vipType, $params = [])
    {
        logInfo("qqqqqqqqqqqqq", $vipType);
        if (is_numeric($vipType)) {
            $vip_type = UserVipFactory::getTypeIdByid_new($vipType);
            $price = UserVipFactory::getReVipAmountByid_new($vipType);

        } else {
            $vip_type = UserVipFactory::getTypeIdByNid($vipType);
            $price = UserVipFactory::getReVipAmountByNid($vipType);
        }
        // $typeId = UserVipFactory::getSubtypeIdByNid($vipType);

        $message = UserVip::select()->where(['user_id' => $uid])->first();
        logInfo("KKKKKKKKKK", ['message' => $message, 'price' => $price]);

        //根据不同类型处理不同的数据
        if (is_numeric($vipType)) {
            $data = UserVipStrategy::getDiffVipTypeDeal_new($message, $vipType);

        } else {
            $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);

        }
        $time = isset($data['time']) ? $data['time'] : date('Y-m-d H:i:s', UserVipFactory::getVipExpiredByNid($vipType));
        $endTime = $params['end_time'] ?? $time;
        $vip_type = $params['vip_type'] ?? $vip_type;
        $update = [
            'vip_type' => $vip_type,
            'subtype_id' => $vipType,
            'status' => $status,
            'open_time' => date('Y-m-d H:i:s', time()),
            'start_time' => date('Y-m-d H:i:s', time()),
            'end_time' => $endTime,  //date('Y-m-d H:i:s', UserVipStrategy::getVipExpired()),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
            'total_consuming' => DB::raw("total_consuming + " . $price),//isset($message['total_consuming']) ? ($message['total_consuming'] + $price) : $price,
            'total_count' => DB::raw("total_count + 1"),//isset($message['total_count']) ? ($message['total_count'] + 1) : 1,
        ];

        if (!empty($outCardId)) {
            $update['vip_no'] = $outCardId;
        }

        logInfo('user_vip update info', $update);

        $res = UserVip::where(['user_id' => $uid])->update($update);

        logInfo('user_vip update result', ['res' => $res]);

        return ($res > 0) ? true : false;
    }

    /**
     * 更新订单表
     *
     * @param array $data
     * @return bool
     */
    public static function updateUserOrderStatus($data = [])
    {
        $responseTxt = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = UserOrder::where(['orderid' => $data['orderid']])->update([
            'payment_order_id' => $data['yborderid'],
            'orderid' => $data['orderid'],
            'lastno' => $data['lastno'],
            //   'cardtype' => $data['cardtype'],
            'amount' => number_format($data['amount'] / 100, 2),
            'status' => $data['status'],
            'response_text' => $responseTxt,
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ]);
        logInfo("aaaaaaaaaaaaaaaaaaaaaaaaa--", ['amount' => $data['amount']]);
        return ($res > 0) ? true : false;
    }

    public static function updateUserOrderStatus_new($data = [])
    {
        $responseTxt = json_encode($data, JSON_UNESCAPED_UNICODE);
        logInfo("aaaaaaaaaaaaaaaaaaaaaaaaa--", ['data' => $data]);

        $res = UserOrder::where(['orderid' => $data['orderid']])->update([
            'payment_order_id' => $data['yborderid'],
            'orderid' => $data['orderid'],
            'lastno' => $data['lastno'],
            //   'cardtype' => $data['cardtype'],
            'amount' => $data['amount'],
            'status' => $data['status'],
            'response_text' => $responseTxt,
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ]);
        logInfo("bbbbbbbbbbbbbbbbbbbbbbbbbb--" , $res);
        return ($res > 0) ? true : false;
    }


    /**
     * 根据订单号获取uid
     *
     * @param $orderId
     * @return mixed
     */
    public static function getUserOrderUid($orderId)
    {
        return UserOrder::where(['orderid' => $orderId])->value('user_id');
    }

    /**
     * 创建订单
     *
     * @param array $data
     * @return mixed
     */
    public static function createOrder($data = [])
    {
        return UserOrder::updateOrCreate($data);
    }

    /**
     * 子类型
     * 根据唯一标识获取有效期
     *
     * @param string $nid
     * @return int
     */
    public static function getSubVipTimeByNid($nid = '')
    {
        $value = UserVipSubtype::where(['type_nid' => $nid, 'status' => 1])->value('period');
        return isset($value) ? $value : 0;
    }

    /**
     * 支付类型
     *
     * @param array $params
     * @return array
     */
    public static function fetchAccountPayment($params = [])
    {
        $payments = AccountPaymentType::from('sd_account_payment_type as t')
            ->join('sd_account_payment as p', 'p.style', '=', 't.id')
            ->select('p.name', 'p.nid', 'p.merchant_code', 'p.merchant_public_key', 'p.merchant_private_key', 'p.channel_public_key', 'p.channel_private_key', 'p.litpic')
            ->where(['t.type_nid' => $params['type_nid'], 't.status' => 1, 'p.nid' => $params['nid'], 'p.status' => 1])
            ->first();
        return $payments ? $payments->toArray() : [];
    }

    /**
     * 支付类型
     *
     * @param array $params
     * @return array
     *  by xuyj 2019-02-21 v3.2.3
     */
    public static function fetchAccountPayment_new($params = [])
    {
        logInfo("33333333-", $params);
        $payments = AccountPaymentType::from('sd_account_payment_type as t')
            ->join('sd_account_payment as p', 'p.style', '=', 't.id')
            ->select('p.name', 'p.nid', 'p.merchant_code', 'p.merchant_public_key', 'p.merchant_private_key', 'p.channel_public_key', 'p.channel_private_key', 'p.litpic', 'p.ishuijupay', 'p.is_wechat_pay')
            ->where(['t.type_nid' => $params['type_nid'], 't.status' => 1, 'p.nid' => $params['nid'], 'p.status' => 1])
            ->first();
        logInfo("33333333333", $payments);
        return $payments ? $payments->toArray() : [];
    }

    /**
     * 根据订单号查询订单是否支付成功
     *
     * @param array $params
     * @return int
     */
    public static function fetchPaymentStatusByOrderId($params = [])
    {
        $status = UserOrder::select(['id'])
            ->where(['orderid' => $params['orderNum'], 'user_id' => $params['userId']])
            ->where(['status' => 1])
            ->first();

        return $status ? 1 : 0;
    }

    /**
     * 判断当前启用哪种支付通道
     *
     * @param array $params
     * @return int
     */
    public static function fetchPaymentChalChoice()
    {
        $status = AccountPayment::select(['ishuijupay', 'is_wechat_pay'])
            ->where(['nid' => "HJZFNEW", 'status' => "1"])
            ->first();

        return $status ? $status->toArray() : [];
        //  return !empty($status )? 1 : 0;
    }

    // 通过汇聚的订单ID 查询该订单的相关信息
    // by xuyj v3.2.3
    public static function fetchOrderInfoByOrderId($orderid, $userid)
    {
        logInfo("orderid 111= ", $orderid);
        $status = UserOrder::select(['*'])
            ->where(['orderid' => $orderid, 'user_id' => $userid])
            ->where(['status' => 0])
            ->first();
        return $status ? $status->toArray() : [];
    }


    // 通过汇聚的订单ID 查询该订单的相关信息
    // by xuyj v3.2.3
    public static function fetchOrderInfoByOrderId_new($orderid, $userid)
    {
        logInfo("orderid 111= ", $orderid);
        $status = UserOrder::select(['*'])
            ->where(['orderid' => $orderid, 'user_id' => $userid])
            ->where(['status' => 0])
            ->first();
        return $status ? $status->toArray() : [];
    }

}