<?php

namespace App\Strategies;

use App\Constants\PaymentConstant;
use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\Chain\Order\ReportOrder\DoReportOrderLogicHandler;
use App\Models\Chain\Order\SubVipOrder\DoSubVipOrderLogicHandler;
use App\Models\Chain\Payment\SubVipOrder\DoSubVipOrderHandler;
use App\Models\Chain\Order\VipOrder\DoVipOrderLogicHandler;
use App\Models\Chain\Payment\ReportOrder\DoReportOrderHandler;
use App\Models\Chain\Payment\VipOrder\DoVipOrderHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\huijubanksType;
use App\Models\Orm\UserBanks;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserVip;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use Monolog\Handler\SlackbotHandlerTest;

/**
 * payment
 *
 * @package App\Strategies
 */
class PaymentStrategy extends AppStrategy
{
    /**
     * 根据不同的订单回调地址参数不同
     *
     * @param $type
     * @return string
     */
    public static function getDiffOrderCallback($type)
    {
        switch ($type) {
            //默认会员
            case UserVipConstant::ORDER_TYPE:
                $param = PaymentStrategy::getUrlParams(UserVipConstant::VIP_TYPE_NID);
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $param = PaymentStrategy::getUrlParams(UserVipConstant::VIP_ANNUAL_MEMBER);
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $param = PaymentStrategy::getUrlParams(UserVipConstant::VIP_QUARTERLY_MEMBER);
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                $param = PaymentStrategy::getUrlParams(UserVipConstant::VIP_MONTHLY_MEMBER);
                break;
            //报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                //添加一些参数
                $param = "";
                break;
            default:
                $param = "";
        }

        return $param;
    }

    /**
     * 对支付回调地址添加,vip类型参数
     *
     * @param $vipTypeNid
     * @return string
     */
    public static function getUrlParams($vipTypeNid)
    {
        switch ($vipTypeNid) {
            case UserVipConstant::VIP_TYPE_NID:
                $str = '&vip_type=' . $vipTypeNid;
                break;
            //年度会员
            case UserVipConstant::VIP_ANNUAL_MEMBER:
                $str = '&vip_type=' . $vipTypeNid;
                break;
            //季度会员
            case UserVipConstant::VIP_QUARTERLY_MEMBER:
                $str = '&vip_type=' . $vipTypeNid;
                break;
            //月度会员
            case UserVipConstant::VIP_MONTHLY_MEMBER:
                $str = '&vip_type=' . $vipTypeNid;
                break;
            default:
                $str = '&vip_type=' . UserVipConstant::VIP_TYPE_NID;
        }

        return $str;
    }

    /**
     * 根据不同的订单类型处理的责任链不同
     *
     * @param $order
     * @return mixed
     */
    public static function getDiffOrderTypeChain($order)
    {
        switch ($order['type']) {
            //会员
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_ANNUAL_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_QUARTERLY_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_MONTHLY_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            //信用报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                $chain = new DoReportOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            default:
                $result = ['error' => RestUtils::getErrorMessage(1139), 'code' => 1139];
        }

        return $result;
    }

    /**
     * 根据不同的订单类型处理的责任链不同
     *
     * @param $order
     * @return mixed
     *  by xuyj v3.2.3
     */
    public static function getDiffOrderTypeChain_new($order)
    {
        switch ($order['type']) {
            //会员
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_ANNUAL_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest_new();
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_QUARTERLY_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest_new();
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                $order['subtypeNid'] = UserVipConstant::VIP_MONTHLY_MEMBER;
                $chain = new DoSubVipOrderLogicHandler($order);
                $result = $chain->handleRequest_new();
                break;
            //信用报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                logInfo("32453465456756434345e-----", $order);
                $chain = new DoReportOrderLogicHandler($order);
                $result = $chain->handleRequest_new();
                break;
            default:
                $result = ['error' => RestUtils::getErrorMessage(1139), 'code' => 1139];
        }
        return $result;
    }

    /**
     * 根据不同的类型，处理不同的责任链
     *
     * @param $params
     * @return mixed
     */
    public static function getDiffOrderChain($params)
    {
        switch ($params['type']) {
            //默认会员
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //信用报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                $chain = new DoReportOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            default:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
        }

        return $result;
    }

    public static function getDiffOrderChain_wechat($params)
    {
        switch ($params['type']) {
            //默认会员
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                logInfo("tytttttttttttttttttttttttt");
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //信用报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                $chain = new DoReportOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            default:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
        }

        return $result;
    }


    public static function getDiffOrderChain_new($params)
    {
        switch ($params['type']) {
            //默认会员
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                logInfo("tytttttttttttttttttttttttt");
                $chain = new DoSubVipOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            //信用报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                logInfo("TTTTTTTTTTTTTTTT");
                $chain = new DoReportOrderHandler($params);
                $result = $chain->handleRequest_new();
                break;
            default:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
        }

        return $result;
    }


    // 更新上次支付卡
    // by xuyj v3.2.3
    public static function updateLastCardStatus($bankcard)
    {
  //      UserBanks::select(['user_id'])->value()
        $message = UserOrder::select(['user_id'])->where(['account' => $bankcard])->first();
        logInfo("99999999999999999999--".$message['user_id']);
        $res = UserBanks::where(['user_id' => $message['user_id']])->update([
            'card_last_status' => "0",
            'hjcard_default'=>"0",
        ]);

        if(!empty($res)){
            $res = UserBanks::where(['account' => $bankcard])->update([
                'card_last_status' => "1",
                'hjcard_default'=>"1",
                'user_id'=> $message['user_id'],
            ]);
            if(!empty($res)){
                logInfo("666666666666666666");
                return true;
            }
        }else{
            $res = UserBanks::where(['account' => $bankcard])->update([
                'card_last_status' => "1",
                'hjcard_default'=>"1",
            ]);
            if(!empty($res)){
                logInfo("666666666666666666");
                return true;
            }
        }
    }


    public static function updateLastCardStatusByOrderid($bankcard)
    {
        //      UserBanks::select(['user_id'])->value()
        $message = UserOrder::select(['user_id','card_num'])->where(['orderid' => $bankcard])->first();
        logInfo("99999999999999999999--".$message['user_id']);
        $res = UserBanks::where(['user_id' => $message['user_id']])->update([
            'card_last_status' => "0",
            'hjcard_default'=>"0",
        ]);

        if(!empty($res)){
            $res = UserBanks::where(['account' => $message['card_num'],'user_id'=> $message['user_id'],'status'=>"0"])->update([
                'card_last_status' => "1",
                'hjcard_default'=>"1",

            ]);
            if(!empty($res)){
                logInfo("666666666666666666");
                return true;
            }
            return true;
        }else{
            $res = UserBanks::where(['account' => $message['card_num']])->update([
                'card_last_status' => "1",
                'hjcard_default'=>"1",
            ]);
            if(!empty($res)){
                logInfo("666666666666666666");
                return true;
            }
            return true;
        }
    }

    // 获取改卡属于哪个银行
    // by xuyj v3.2.3
    public static function getBanknameByabr($bankabr){
        $res = huijubanksType::select(['bankname','bankid'])->where(['bankabr' => $bankabr])->first();
        return $res?$res->toArray():[];
    }

    // 汇聚支付支持的银行信息
    // by xuyj v3.2.3
    public static function fetchHuiJuPaySuportBank(){
        $res = huijubanksType::select();
        $arr = $res
            ->get()->toArray();
     //   $banks['list'] = $arr;
        return $arr?$arr:[];
    }

    /**
     * 生成订单参数
     *
     * @param array $data
     * @return array
     */
    public static function orderYibaoParams($data = [], $params = [])
    {
        return [
            'orderid' => $data['order_id'],
            'transtime' => time(),
            'amount' => $params['amount'],
            'productcatalog' => '1',
            'productname' => $params['productname'],
            'productdesc' => $params['productdesc'],
            'identitytype' => 2,//用户id
            'identityid' => $data['user_id'],
            'terminaltype' => 0,
            'terminalid' => $data['terminal_id'],
            'userip' => Utils::ipAddress(),
            'directpaytype' => $data['pay_type'],
            'userua' => UserAgent::i()->getUserAgent(),
            'fcallbackurl' => AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_SYN . $params['url_params'] . PaymentStrategy::getDiffOrderCallback($params['url_params']),
            'callbackurl' => AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_ASYN . $params['url_params'] . PaymentStrategy::getDiffOrderCallback($params['url_params']),
            'orderexpdate' => $data['order_expired_time'],
            'cardno' => $data['cardno'],
            'idcardtype' => '01',
            'idcard' => $data['idcard'],
            'owner' => $data['owner'],
        ];
    }

    /**
     * 生成汇聚订单参数
     *
     * @param array $data
     * @return array
     *  by xuyj v3.2.3
     */
    public static function orderHuiJuParams_new($data = [], $params = [])
    {

        return [
            'p0_Version'=>"2.0",
            'p1_MerchantNo'=>"888105100000721",
            'p2_MerchantName' => "速贷之家",
            'q1_OrderNo' =>$data['order_id'],
            'q3_Cur' =>"1",
            'q8_FrpCode' =>"FAST",
            's1_PayerName' =>!empty($data['owner'])?$data['owner']:$data['name'],
            's2_PayerCardType' =>"1",
            's3_PayerCardNo' =>$data['idcard'],
            's4_PayerBankCardNo' =>$data['card_num'],
            's7_BankMobile' =>$data['mobile'],
            'q2_Amount' => $params['amount'],

        ];
    }

    /**
     * 生成汇聚订单参数--无需签约 直接支付
     *
     * @param array $data
     * @return array
     *  by xuyj v3.2.3
     */
    public static function orderHuiJuParams_new_Sec($data = [], $params = [],$signid)
    {

        return [
            'p0_Version'=>"2.0",
            'p1_MerchantNo'=>"888105100000721",
            'p2_MerchantName' => "速贷之家",
            'q1_OrderNo' =>$data['order_id'],
            'q3_Cur' =>"1",
            'q8_FrpCode' =>"FAST",
            'q4_ProductName' =>"充值会员",
            'q7_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_QUICK_ASYN,
            's1_PayerName' =>$data['owner'],
            's2_PayerCardType' =>"1",
            's3_PayerCardNo' =>$data['idcard'],
            's4_PayerBankCardNo' =>$data['card_num'],
            's7_BankMobile' =>$data['mobile'],
            'q2_Amount' => $params['amount'],
            's9_bankSignId' =>$signid,

        ];
    }

    public static function orderHuiJuParams_new_Sec_report($data = [], $params = [],$signid)
    {

        return [
            'p0_Version'=>"2.0",
            'p1_MerchantNo'=>"888105100000721",
            'p2_MerchantName' => "速贷之家",
            'q1_OrderNo' =>$data['order_id'],
            'q3_Cur' =>"1",
            'q8_FrpCode' =>"FAST",
            'q4_ProductName' =>"征信报告",
            'q7_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_QUICK_ASYN,
            's1_PayerName' =>$data['owner'],
            's2_PayerCardType' =>"1",
            's3_PayerCardNo' =>$data['idcard'],
            's4_PayerBankCardNo' =>$data['card_num'],
            's7_BankMobile' =>$data['mobile'],
            'q2_Amount' => $params['amount'],
            's9_bankSignId' =>$signid,

        ];
    }

    /**
     * 用户订单表的字段封装
     *
     * @param array $data
     * @param array $params
     * @return array
     */
    public static function getUserOrderParams($data = [], $params = [],$subvipinfo=[])
    {
        logInfo("yyyyyyyyyyyyyyyyyyyyyy---".\GuzzleHttp\json_encode($params));
        return [
            'user_id' => $data['user_id'],
            'bank_id' => isset($data['bank_id']) ? $data['bank_id'] : 0,
            'orderid' => $data['order_id'],
            'payment_order_id' => $data['payment_order_id'],
            'order_expired' => $data['order_expired'],  //订单有效期
            'order_type' => $params['order_type'],//订单类型
            'payment_type' => $params['payment_type'],//支付类型`
            'pay_type' => $data['pay_type'],
            'terminaltype' => PaymentConstant::YIBAO_TERMINAL_TYPE,
            'terminalid' => $data['terminal_id'],
            'card_num' => $data['card_num'],
            'amount' => $params['amount'],//支付金额
            'user_agent' => UserAgent::i()->getUserAgent(),
            'created_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'request_text' => $data['request_text'],
            'subtype' =>"no_default",
            'cardtype'=>isset($data['card_type'])?$data['card_type']:"1",
            'status'=>"0",
        ];
    }

    public static function getUserOrderParams_new($data = [], $params = [],$subvipinfo=[])
    {
        return [
            'user_id' => $data['user_id'],
            'bank_id' => isset($data['bank_id']) ? $data['bank_id'] : 0,
            'orderid' => $data['order_id'],
            'payment_order_id' => $data['payment_order_id'],
            'order_expired' => $data['order_expired'],  //订单有效期
            'order_type' => $params['order_type'],//订单类型
            'payment_type' => $params['payment_type'],//支付类型
            'pay_type' => $data['pay_type'],
            'terminaltype' => PaymentConstant::YIBAO_TERMINAL_TYPE,
            'terminalid' => $data['terminal_id'],
            'card_num' => $data['card_num'],
            'amount' => $params['amount'],//支付金额
            'user_agent' => UserAgent::i()->getUserAgent(),
            'created_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'request_text' => $data['request_text'],
            'subtype' => $subvipinfo,
            'cardtype'=>isset($data['card_type']) ? $data['card_type'] : $data['cardtype'],
            'status'=>"0",
        ];
    }

    public static function getUserOrderParams_report($data = [], $params = [],$subvipinfo=[])
    {
        logInfo("UUUUUUUUUUUUUUUUUUUUU");
        logInfo(json_encode($data));

        logInfo(json_encode($params));
        return [
            'user_id' => $data['user_id'],
            'bank_id' => isset($data['bank_id']) ? $data['bank_id'] : 0,
            'orderid' => $data['order_id'],
            'payment_order_id' => $data['payment_order_id'],
            'order_expired' => $data['order_expired'],  //订单有效期
            'order_type' => $params['order_type'],//订单类型
            'payment_type' => $params['payment_type'],//支付类型
            'pay_type' => $data['pay_type'],
            'terminaltype' => PaymentConstant::YIBAO_TERMINAL_TYPE,
            'terminalid' => $data['terminal_id'],
            'card_num' => $data['card_num'],
            'amount' => $params['amount'],//支付金额
            'user_agent' => UserAgent::i()->getUserAgent(),
            'created_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'request_text' => $data['request_text'],
            'subtype' => "no_default",
            'cardtype'=>isset($data['card_type']) ? $data['card_type'] : $data['cardtype'],
            'status'=>"0",
        ];
    }

    /**
     * 用户订单表的字段封装--汇聚快捷支付 二次支付 订单生成
     *
     * @param array $data
     * @param array $params
     * @return array
     */
    public static function getUserOrderParams_sec($data = [], $params = [],$subvipinfo)
    {
       // logInfo("1111112222222222=".\GuzzleHttp\json_encode($params['subVip']));
        return [
            'user_id' => $data['user_id'],
            'bank_id' => isset($data['bank_id']) ? $data['bank_id'] : 0,
            'orderid' => $data['order_id'],
            'payment_order_id' => $data['payment_order_id'],
            'order_expired' => $data['order_expired'],  //订单有效期
            'order_type' => $params['order_type'],//订单类型
            'payment_type' => $params['payment_type'],//支付类型
            'pay_type' => $data['pay_type'],
            'terminaltype' => PaymentConstant::YIBAO_TERMINAL_TYPE,
            'terminalid' => $data['terminal_id'],
            'card_num' => $data['card_num'],
            'amount' => $params['amount'],//支付金额
            'user_agent' => UserAgent::i()->getUserAgent(),
            'created_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'request_text' => $data['request_text'],
      //      'status' =>"1",
            'subtype' => $subvipinfo,
            'cardtype'=>isset($data['card_type']) ? $data['card_type'] : $data['cardtype'],
        ];
    }

    /**
     * 根据订单状态返回不同的信息
     *
     * @param array $params
     * @return mixed
     */
    public static function getDiffTypeInfo_new($params = [], $datas = [])
    {
        $type = isset($params['orderType']) ? $params['orderType'] : '';
        //会员信息 不是会员信息为空
        $message = isset($params['message']) ? $params['message'] : [];
        //vip_default
        $vipType = isset($params['vipNid']) ? $params['vipNid'] : '';
        //支付宝状态是否存在
        $alipayStatus = isset($datas['alipay_status']) ? $datas['alipay_status'] : 0;

        switch ($type) {
            //默认的年度会员
            case UserVipConstant::ORDER_TYPE:
                $vip = UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID);
                //根据不同类型处理不同的数据
                $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipStrategy::getVipExpired()) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $vip;
                $reList['price_twice'] = floatval($vip);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] =  UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = isset($datas['wechat']) ? $datas['wechat'] : 0;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                //年度会员类型
                $userVipNid = UserVipConstant::VIP_ANNUAL_MEMBER;
                //年度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME_NEW;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = isset($datas['wechat']) ? $datas['wechat'] : 0;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                $reList['quickpay_channel'] = PaymentConstant::PAYMENT_HUIJU_QUICKPAY_NID;
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                //季度会员类型
                $userVipNid = UserVipConstant::VIP_QUARTERLY_MEMBER;
                //年度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME_NEW;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] =isset($datas['wechat']) ? $datas['wechat'] : 0;// PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                //月度会员类型
                $userVipNid = UserVipConstant::VIP_MONTHLY_MEMBER;
                //月度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME_NEW;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = isset($datas['wechat']) ? $datas['wechat'] : 0;//PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                $report = UserReportFactory::fetchReportPrice();
                $reList['price'] = $report;
                $reList['price_twice'] = floatval($report);
                $reList['business_name'] = UserReportConstant::REPORT_MEMBER_NAME;
                $reList['bug_name'] = UserReportConstant::REPORT_ORDER_PRODUCT_NAME;
                $reList['expired_time'] = UserReportConstant::REPORT_PRODUCT_VALIDITY;
                $reList['wechat'] = isset($datas['wechat']) ? $datas['wechat'] : 0;//PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            default:
                $reList = [];
        }

        return $reList;
    }


    public static function getDiffTypeInfo($params = [], $datas = [])
    {
        $type = isset($params['orderType']) ? $params['orderType'] : '';
        //会员信息 不是会员信息为空
        $message = isset($params['message']) ? $params['message'] : [];
        //vip_default
        $vipType = isset($params['vipNid']) ? $params['vipNid'] : '';
        //支付宝状态是否存在
        $alipayStatus = isset($datas['alipay_status']) ? $datas['alipay_status'] : 0;

        switch ($type) {
            //默认的年度会员
            case UserVipConstant::ORDER_TYPE:
                $vip = UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID);
                //根据不同类型处理不同的数据
                $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipStrategy::getVipExpired()) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $vip;
                $reList['price_twice'] = floatval($vip);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //年度会员
            case UserVipConstant::ORDER_VIP_ANNUAL_MEMBER:
                //年度会员类型
                $userVipNid = UserVipConstant::VIP_ANNUAL_MEMBER;
                //年度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //季度会员
            case UserVipConstant::ORDER_VIP_QUARTERLY_MEMBER:
                //季度会员类型
                $userVipNid = UserVipConstant::VIP_QUARTERLY_MEMBER;
                //年度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //月度会员
            case UserVipConstant::ORDER_VIP_MONTHLY_MEMBER:
                //月度会员类型
                $userVipNid = UserVipConstant::VIP_MONTHLY_MEMBER;
                //月度会员
                $price = UserVipFactory::getReVipAmountByNid($userVipNid);
                //根据不同类型处理得到有效期时间
                $data = UserVipStrategy::getDiffVipPeriodOfValidity($message, $userVipNid);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipFactory::getVipExpiredByNid($userVipNid)) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $price;
                $reList['price_twice'] = floatval($price);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            //报告
            case UserReportConstant::REPORT_ORDER_TYPE:
                $report = UserReportFactory::fetchReportPrice();
                $reList['price'] = $report;
                $reList['price_twice'] = floatval($report);
                $reList['business_name'] = UserReportConstant::REPORT_MEMBER_NAME;
                $reList['bug_name'] = UserReportConstant::REPORT_ORDER_PRODUCT_NAME;
                $reList['expired_time'] = UserReportConstant::REPORT_PRODUCT_VALIDITY;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                $reList['alipay_status'] = $alipayStatus;
                break;
            default:
                $reList = [];
        }

        return $reList;
    }

    /**
     * 获取订单相同的部分
     *
     * @param $order
     * @param $bankCardInfo
     * @return mixed
     */
    public static function getOrderSameSection($order, $bankCardInfo = [])
    {
        $realnameType = isset($order['realnameType']) ? $order['realnameType'] : '';
        //实名步骤
        $order['step'] = UserIdentityStrategy::getRealnameStep($realnameType);
        //获取用户信息，银行卡，银行id
        $userAuthInfo = UserIdentityFactory::fetchIdcardAuthenInfoByStatus($order);

        $account = isset($bankCardInfo['account']) ? $bankCardInfo['account'] : '';
        $order['idcard'] = isset($userAuthInfo['certificate_no']) ? $userAuthInfo['certificate_no'] : '';
        $order['owner'] = isset($userAuthInfo['realname']) ? $userAuthInfo['realname'] : '';
        $order['cardno'] = ($order['pay_type'] == 3) ? $account : '';
        //根据支付类型创建订单号
        $order['order_id'] = PaymentService::i($order)->fetchOrderId();; //TODO:生成orderid
        $order['order_expired_time'] = PaymentConstant::ORDER_EXPIRED_MINUTE;
        //订单过期显示的时间
        $order['order_expired'] = date('Y-m-d H:i:s', strtotime("+{$order['order_expired_time']} minutes"));
        $order['card_num'] = !empty($account) ? $account : '';

        return $order;
    }
    /**
     * 获取订单相同的部分
     *
     * @param $order
     * @param $bankCardInfo
     * @return mixed
     */
    public static function getOrderSameSection_new($order, $bankCardInfo = [])
    {
        $realnameType = isset($order['realnameType']) ? $order['realnameType'] : '';
        //实名步骤
        $order['step'] = UserIdentityStrategy::getRealnameStep($realnameType);
        //获取用户信息，银行卡，银行id
        $userAuthInfo = UserIdentityFactory::fetchIdcardAuthenInfoByStatus_new($order);
        $account = isset($bankCardInfo['account']) ? $bankCardInfo['account'] : '';
        $order['idcard'] = isset($userAuthInfo['certificate_no']) ? $userAuthInfo['certificate_no'] : $order['idcard'];
        $order['owner'] = isset($userAuthInfo['realname']) ? $userAuthInfo['realname'] : '';
        $order['cardno'] = ($order['pay_type'] == 4) ? $account : '';
        //根据支付类型创建订单号
        $order['order_id'] = PaymentService::i($order)->fetchOrderId_huiju(); //TODO:生成orderid
        $order['order_expired_time'] = PaymentConstant::ORDER_EXPIRED_MINUTE;

        //订单过期显示的时间
        $order['order_expired'] = date('Y-m-d H:i:s', strtotime("+{$order['order_expired_time']} minutes"));

        $order['card_num'] = !empty($account) ? $account : '';

        $order['card_type'] = isset($bankCardInfo['card_type'])?$bankCardInfo['card_type']:"0";

        logInfo("BBBBBBBBgetOrderSameSec  : ".$order['order_id']);

        $order['cvv2'] = isset($bankCardInfo['cvv2'])?$bankCardInfo['cvv2']:"0";

        $order['avatime'] = isset($bankCardInfo['avatime'])?$bankCardInfo['avatime']:"0";
        $order['mobile'] = isset($bankCardInfo['card_mobile'])?$bankCardInfo['card_mobile']:"0";
        logInfo("zzzzzzzzgetOrderSameSec  OrderInfo : ".\GuzzleHttp\json_encode($order));
        return $order;
    }

    public static function getOrderSameSection_new_xcx($order, $bankCardInfo = [])
    {

        $realnameType = isset($order['realnameType']) ? $order['realnameType'] : '';
        //实名步骤
        $order['step'] = UserIdentityStrategy::getRealnameStep($realnameType);
        //获取用户信息，银行卡，银行id
        logInfo("AAAAAAgetOrderSameSection func line 514 : ");
        $userAuthInfo = UserIdentityFactory::fetchIdcardAuthenInfoByStatus_new($order);
        $account = isset($bankCardInfo['account']) ? $bankCardInfo['account'] : '';
        $order['idcard'] = isset($userAuthInfo['certificate_no']) ? $userAuthInfo['certificate_no'] : '';
        $order['owner'] = isset($userAuthInfo['realname']) ? $userAuthInfo['realname'] : '';
        $order['cardno'] = ($order['pay_type'] == 4) ? $account : '';
        logInfo("hhhhhhhhhhhhhhhhhhhhhh----");

        //根据支付类型创建订单号
        $order['order_id'] = PaymentService::i($order)->fetchOrderId_xcx(); //TODO:生成orderid
        $order['order_expired_time'] = PaymentConstant::ORDER_EXPIRED_MINUTE;
        //订单过期显示的时间
        $order['order_expired'] = date('Y-m-d H:i:s', strtotime("+{$order['order_expired_time']} minutes"));

        $order['card_num'] = !empty($account) ? $account : '';
        $order['card_type'] = "0";
        $order['cvv2'] = "0";
        logInfo("BBBBBBBBgetOrderSameSec  : ".$order['order_id']);
        $order['avatime'] ="0";
        $order['mobile'] = "0";
        logInfo("zzzzzzzzgetOrderSameSec  OrderInfo : ".\GuzzleHttp\json_encode($order));
        return $order;
    }



    /**
     * 获取会员编号
     *
     * @param int $lastId 最后一个ID
     * @param string $prefix 前缀
     * @param string $name 名称
     * @param int $num 编号数字
     * @return string
     */
    public static function generateId($lastId, $name = 'VIP', $prefix = 'SD', $num = 8)
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $timeLength = date("YmdHis") . $millisecond;

        $length = PaymentConstant::PAYMENT_PRODUCT_NUMBER_LENGTH - strlen(trim($prefix)) - strlen(trim($name)) - strlen(trim($timeLength)) - $num - 2;

        //如果还有多余的长度获取随机字符串
        $str = '';
        if ($length > 0) {
            $str = PaymentService::i()->getRandString($length);
        } else {
            $name = substr($name, 0, $length);
        }

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . '-' . $name . '-' . $str . $timeLength . $strNum;
    }

    /**
     * 生成前端报告编号
     *
     * @param $lastId
     * @param string $prefix
     * @param int $num
     * @return string
     */
    public static function generateFrontId($lastId, $prefix = 'SD', $num = 3)
    {
        $timeLenght = date('ymd', time());

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . $timeLenght . $strNum;
    }

    /**
     * 汇聚订单下单请求参数汇总
     *
     * @param $requestParams
     * @param array $otherParams
     * @return mixed
     */
    public static function orderHuijuParams($requestParams, $otherParams = [])
    {
        //支付金额
        $requestParams['amount'] = $otherParams['amount'];
        $requestParams['productname'] = $otherParams['productname'];
        $requestParams['productdesc'] = $otherParams['productdesc'];
        $requestParams['orderNo'] = $otherParams['orderNo'];
        $requestParams['urlParams'] = $otherParams['url_params'];

        return $requestParams;
    }

    /**
     * 汇聚支付公用回传参数处理
     *
     * @param array $params
     * @return array
     */
    public static function getHuijuCallbackParams($params = [])
    {
        $mp = urldecode($params['r5_Mp']);
        $mp = json_decode($mp, true);
        $params['type'] = isset($mp['type']) ? $mp['type'] : '';
        $params['vip_type'] = isset($mp['vip_type']) ? $mp['vip_type'] : '';

        return $params ? $params : [];
    }

}