<?php

namespace App\Models\Chain\Order\ReportOrder;

use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Services\AppService;
use App\Services\Core\Payment\HuiJu\HuiJuUtil;
use App\Services\Core\Payment\PaymentService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserReportStrategy;
use App\Strategies\UserVipStrategy;

class CreateReportOrderAction extends AbstractHandler
{

    private $params = array();
    private $backInfo = array();
    protected $error = array('error' => '创建报告订单失败！', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:创建订单
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createReportOrder($this->params)) {
            $this->setSuccessor(new BankCardAction($this->params, $this->backInfo));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    public function handleRequest_new()
    {
        if ($this->createReportOrder_new($this->params)) {
            $this->setSuccessor(new BankCardAction($this->params, $this->backInfo));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }
    /**
     * 创建vip订单
     *
     * @param array $params
     * @param array $vip
     * @return bool
     */
    private function createReportOrder($params = [])
    {
        $payType = $params['pay_type'];

        //支付类型 支付类型（1，微信 2，支付宝 3，一键支付）
        $res = [];
        switch ($payType) {
            case 1: //微信
                break;
            case 2: //支付宝
                $res = $this->alipay($params);
                break;
            case 3: //快捷支付
                $res = $this->quickPayment($params);
                break;
        }

        return $res;
    }


    // by xuyj v3.2.3
    private function createReportOrder_new($params = [])
    {
        logInfo('createReportOrder_new', $params);
        $payType = $params['pay_type'];

        //支付类型 支付类型（1，微信 2，支付宝 3，一键支付）
        $res = [];
        switch ($payType) {
            case 1: //微信
                logInfo("4444444444444444444444444444444444");

                $res = $this->wechatpay_mini($params);
                break;
            case 2: //支付宝
                $res = $this->alipay($params);
                break;
            case 3: //快捷支付
                $res = $this->quickPayment($params);
                break;
            case 4: // 汇聚支付
                if(strcmp($params['isBangCard'],"1")==0){
                    $bankCardInfo = array();
                    $bankCardInfo['user_id'] = $params['user_id'];
                    $bankCardInfo['bank_id'] = $params['bankid'];
                    $bankCardInfo['account'] =$params['bankcard'];
                    $bankCardInfo['bank_name'] =$params['bankname'];
                    $bankCardInfo['sbank_name'] ="";
                    $bankCardInfo['branch'] ="";
                    $bankCardInfo['province'] ="";
                    $bankCardInfo['city'] ="";
                    $bankCardInfo['area'] ="";
                    $bankCardInfo['card_type'] =$params['cardtype'];
                    $bankCardInfo['card_default'] ="0";
                    $bankCardInfo['card_use'] ="1";
                    $bankCardInfo['card_last_status'] ="1";
                    $bankCardInfo['card_mobile'] =$params['mobile'];
                    $bankCardInfo['created_ip'] ="192.168.1.1";
                    $bankCardInfo['updated_ip'] ="192.168.1.1";
                    $bankCardInfo['from'] ="";
                    $bankCardInfo['status'] ="0";
                    $bankCardInfo['created_at'] ="1970-01-01 00:00:00";
                    $bankCardInfo['updated_at'] ="1970-01-01 00:00:00";
                    $bankCardInfo['huiju_paycount'] ="0";
                    $bankCardInfo['cvv2'] =$params['cvv2'];
                    $bankCardInfo['avatime'] =$params['avatime'];
                    $bankCardInfo['huijusignid'] ="0";
                    $bankCardInfo['hjcard_default'] ="0";
                    if(strcmp($params['cardtype'],"2")==0){
                        if(strlen($params['avatime'])>=3 && strcmp($params['avatime'],"0")!=0){
                            $bankCardInfo['avatime'] =$this->insertToStr($params['avatime']);
                            $bankCardInfo['cvv2'] =$params['cvv2'];

                        }else{
                            $bankCardInfo['avatime'] ="";
                        }
                    }else{
                        $bankCardInfo['avatime']="";
                        $bankCardInfo['cvv2'] =$params['cvv2'];
                    }
                    if(intval($bankCardInfo['huiju_paycount'])==0){
                        $res = $this->quickPayment_huiju($params,$bankCardInfo);
                    }else if(intval($bankCardInfo['huiju_paycount'])>0){
                        $res = $this->quickPayment_huiju_sec($params);
                    }
                }else{
                    $bankCardInfo = UserBankCardFactory::getBankCardInfo($params['bankcard_id'], $params['user_id']);
                    if(intval($bankCardInfo['huiju_paycount'])==0){
                        $res = $this->quickPayment_huiju($params,$bankCardInfo);
                    }else if(intval($bankCardInfo['huiju_paycount'])>0){
                        $res = $this->quickPayment_huiju_sec($params);
                    }
                }

              //  $res = $this->quickPayment_huiju($params,$ayyay);
                break;
        }

        return $res;
    }


    /**
     * 微信小程序支付--汇聚支付
     * 调用第三方汇聚支付
     *
     * @param array $params
     * @return bool
     *  by xuyj v3.2.3
     */
    private function wechatpay_mini($params=[]){
        $bankCardInfo = [];

     //   $subvipinfo = \GuzzleHttp\json_encode($params['subVip']);

        $params = PaymentStrategy::getOrderSameSection_new_xcx($params, $bankCardInfo);
        //订单号

        $back['orderNum'] = $params['order_id'];

        //支付支付金额&产品描述
        $otherParams = UserReportStrategy::getHuijuReportOtherParams($params);

        //汇聚支付请求数据
        $requestParams = HuiJuUtil::i()->getOrderPayParams($otherParams);
        //请求数据汇总

        $requestParams = PaymentStrategy::orderHuijuParams($requestParams, $otherParams);
        //汇聚支付下单接口
        $ret = PaymentService::i($params)->order_wechat($requestParams);
        if (empty($ret)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1136), 'code' => 1136];
            return false;
        }

        //入库参数处理
        $params['request_text'] = json_encode($requestParams, JSON_UNESCAPED_UNICODE);
        //支付平台生成的流水号
        $params['payment_order_id'] = $ret['r7_TrxNo'];
        //商户订单号
        $params['orderId'] = $ret['r2_OrderNo'];
        //QQ、支付宝 H5 支付返回支付链接,该链接在手机端打开,可以直接调起 QQ 钱包或支付宝进行支付
        $back['payurl'] = $ret['rc_Result'];
        //回调地址
        //  $back['fcallbackurl'] = AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_SYN;
        //创建订单
        //汇聚支付唯一标识
        $params['payment_nid'] = UserVipConstant::PAYMENT_TYPE_HUIJU;
        $params['amount'] = $ret['r3_Amount'];
        $orderOtherParams = UserVipStrategy::getUserOrderOtherParamsByParam_new($params);

        $reOrder = PaymentStrategy::getUserOrderParams($params, $orderOtherParams);
        $reOrder['card_num']="0";
        $reOrder['subtype']  = "no_default";


        $createOrder = PaymentFactory::createOrder($reOrder);
          if (!$createOrder) {
               $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
              return false;
          }
        //将返回结果赋值
        $this->backInfo = $back;

        return $back;
    }


    /**
     * 支付宝支付
     * 调用第三方汇聚支付
     *
     * @param array $params
     * @return bool
     */
    private function alipay($params = [])
    {
        $bankCardInfo = [];
        $params = PaymentStrategy::getOrderSameSection($params, $bankCardInfo);
        //订单号
        $back['orderNum'] = $params['order_id'];
        //支付支付金额&产品描述
        $otherParams = UserReportStrategy::getHuijuReportOtherParams($params);
        //汇聚支付请求数据
        $requestParams = HuiJuUtil::i()->getOrderPayParams($otherParams);
        //请求数据汇总
        $requestParams = PaymentStrategy::orderHuijuParams($requestParams, $otherParams);

        //汇聚支付下单接口
        $ret = PaymentService::i($params)->order($requestParams);

        if (empty($ret)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1136), 'code' => 1136];
            return false;
        }

        //入库参数处理
        $params['request_text'] = json_encode($requestParams, JSON_UNESCAPED_UNICODE);
        //支付平台生成的流水号
        $params['payment_order_id'] = $ret['r7_TrxNo'];
        //商户订单号
        $params['orderId'] = $ret['r2_OrderNo'];
        //QQ、支付宝 H5 支付返回支付链接,该链接在手机端打开,可以直接调起 QQ 钱包或支付宝进行支付
        $back['payurl'] = $ret['rc_Result'];
        //回调地址
        $back['fcallbackurl'] = AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_SYN;
        //创建订单
        //汇聚支付唯一标识
        $params['payment_nid'] = UserVipConstant::PAYMENT_TYPE_HUIJU;
        $orderOtherParams = UserVipStrategy::getUserOrderOtherParamsByParam($params);
        $reOrder = PaymentStrategy::getUserOrderParams($params, $orderOtherParams);

        $createOrder = PaymentFactory::createOrder($reOrder);
        if (!$createOrder) {
            $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
            return false;
        }
        //将返回结果赋值
        $this->backInfo = $back;

        return $back;
    }

    /**
     * 快捷支付 - 易宝支付
     *
     * @param array $params
     * @return bool
     */
    private function quickPayment($params = [])
    {
        //如果是银行卡支付
        $bankCardInfo = [];
        if ($params['pay_type'] == 3) {
            $bankCardInfo = UserBankCardFactory::getBankCardInfo($params['bankcard_id'], $params['user_id']);
            if (!$bankCardInfo) {
                $this->error = ['error' => RestUtils::getErrorMessage(1135), 'code' => 1135];
                return false;
            }
            $params['bank_id'] = $bankCardInfo['bank_id'];
        }

        $params = PaymentStrategy::getOrderSameSection($params, $bankCardInfo);
        //订单号
        $back['orderNum'] = $params['order_id'];
        $amount = UserReportFactory::fetchReportPrice();
        //调用易宝订单
        $otherParams = UserReportStrategy::getYibaoOtherParams();
        $yiBaoparams = PaymentStrategy::orderYibaoParams($params, $otherParams);//UserVipStrategy::orderYibaoParams($order);
        $ret = PaymentService::i($params)->order($yiBaoparams);

        if (empty($ret)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1136), 'code' => 1136];
            return false;
        }

        $params['request_text'] = json_encode($yiBaoparams, JSON_UNESCAPED_UNICODE);
        $params['payment_order_id'] = $ret['yborderid'];
        $params['orderId'] = $ret['orderid'];
        $back['payurl'] = $ret['payurl'];
        $back['fcallbackurl'] = AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_SYN . $params['type'] . PaymentStrategy::getDiffOrderCallback($params['type']);
        //创建订单
        //易宝支付唯一标识
        $params['payment_nid'] = UserVipConstant::PAYMENT_TYPE;
        $parmReq = \Qiniu\json_decode($params['request_text'],true);
        $params['amount'] =$amount;
        $orderOtherParams = UserVipStrategy::getUserOrderOtherParamsByParam($params);
        $reOrder = PaymentStrategy::getUserOrderParams($params, $orderOtherParams);

        $createOrder = PaymentFactory::createOrder($reOrder);
        if (!$createOrder) {
            $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
            return false;
        }
        //将返回结果赋值
        $this->backInfo = $back;

        return $back;
    }


    // 生成 有效期固定格式
    // by xuyj v3.2.3
    private function insertToStr($str){
        //指定插入位置前的字符串
        /* $startstr="";
         for($j=0; $j<$i; $j++){
             $startstr .= $str[$j];
         }

         //指定插入位置后的字符串
         $laststr="";
         for ($j=$i; $j<strlen($str); $j++){
             $laststr .= $str[$j];
         }

         //将插入位置前，要插入的，插入位置后三个字符串拼接起来
         $str = $startstr . $substr . $laststr;*/
        $year = "20";
        for ($j=3; $j<strlen($str); $j++){
            $year .= $str[$j];
        }
        $month = "";
        for ($j=0; $j<2; $j++){
            $month .= $str[$j];
        }
        $str = $year."-".$month;
        //返回结果
        return $str;
    }

    // 生成签名
    // 汇聚支付 生成签名
    // by xuyj v3.2.3
    function hmacRequest($params, $key, $encryptType = "1")
    {
        if ("1" == $encryptType) {
            return md5(implode("", $params) . $key);
        } else {
            $private_key = openssl_pkey_get_private($key);
            $params = implode("", $params);
            openssl_sign($params, $sign, $private_key, OPENSSL_ALGO_MD5);
            openssl_free_key($private_key);
            $sign = base64_encode($sign);
            return $sign;
        }

    }

    /**
     * 快捷支付 - 汇聚支付
     *
     * @param array $params
     * @return bool
     */
    private function quickPayment_huiju($params = [],$bankCardInfonew=[])
    {
        //如果是银行卡支付
        logInfo("55555555555555555555--", $params);
        $bankCardInfo = [];
        if ($params['pay_type'] == 4) {
            if(strcmp($params['isBangCard'],"1")==0){
                $bankCardInfo = $bankCardInfonew;
            }else{
                $bankCardInfo = UserBankCardFactory::getBankCardInfo($params['bankcard_id'], $params['user_id']);
                if (!$bankCardInfo) {
                    $this->error = ['error' => RestUtils::getErrorMessage(1135), 'code' => 1135];
                    return false;
                }
            }

            $params['bank_id'] = $bankCardInfo['bank_id'];
        }
        $pcvv2="";
        $pavatime = "";
        if(strcmp($params['cardtype'],"2")==0){
            $pcvv2 =$params['cvv2'];
            if(strlen($params['avatime'])<6){
                $pavatime =$this->insertToStr($params['avatime']);
            }else{
                $pavatime = $params['avatime'];
            }

        }
        $params = PaymentStrategy::getOrderSameSection_new($params, $bankCardInfo);
        //订单号
        $back['orderNum'] = $params['order_id'];
        //调用易宝订单
   //     $otherParams = UserVipStrategy::getHuiJuOtherParamsByParam_new($params);
        $otherParams = UserReportStrategy::getYibaoOtherParams_huiju();
        $huiJuparams = PaymentStrategy::orderHuiJuParams_new($params, $otherParams);//UserVipStrategy::orderYibaoParams($order);
        if(strcmp($params['cardtype'],"2")==0 && strcmp($params['isBangCard'],"1")==0){
            if(strcmp($params['cvv2'],"0")==0){
                $huiJuparams['s6_CVV2'] = $pcvv2;
            }else{
                $huiJuparams['s6_CVV2'] = $params['cvv2'];
            }
            if(strcmp($params['avatime'],"0")==0){
                $huiJuparams['s5_BankCardExpire'] = $pavatime;
            }else if(strlen($params['avatime'])>=4){
                $huiJuparams['s5_BankCardExpire'] = $params['avatime'];//$avatime;
            }else{
                $huiJuparams['s5_BankCardExpire'] = $params['avatime'];//$avatime;
            }
        }else if(strcmp($params['card_type'],"2")==0   && strcmp($params['isBangCard'],"0")==0){
            if(strcmp($params['cvv2'],"0")==0){
                $huiJuparams['s6_CVV2'] = $pcvv2;
            }else{
                $huiJuparams['s6_CVV2'] = $pcvv2;
            }
            if(strcmp($params['avatime'],"0")==0){
                $huiJuparams['s5_BankCardExpire'] = $pavatime;
            }else if(strlen($params['avatime'])>=4){
                $huiJuparams['s5_BankCardExpire'] = $params['avatime'];//$avatime;
            }else{
                $huiJuparams['s5_BankCardExpire'] = $params['avatime'];//$avatime;
            }
        }
        ksort($huiJuparams);
     //   $hmcVal = urlencode($this->hmacRequest($huiJuparams,"9aec0efceb804391842838bdc420ebbd"));
        $hmcVal= HuiJuUtil::i()->fetchHmacData($huiJuparams);
        $huiJuparams['hmac'] = $hmcVal;
        logInfo("RRRRRRRRRRRRRRRRRRRRRRRRRRRRRRR == : ", $huiJuparams);
        $ret = PaymentService::i($params)->order_new($huiJuparams);
        logInfo("DDDDDDDDDDDDDDDDDDDDDD == : ", $ret);
        if (empty($ret)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1136), 'code' => 1136];
            return false;
        }
        $huiJuparams['productdesc'] = "【购买报告】";
        $params['request_text'] = json_encode($huiJuparams, JSON_UNESCAPED_UNICODE);
        $params['payment_order_id'] = $ret['r3_OrderNo'];
        $params['orderId'] =  $ret['r5_TrxNo'];
     //   $back['payurl'] = $ret['payurl'];
     //   $back['fcallbackurl'] = AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_SYN . $params['type'] . PaymentStrategy::getDiffOrderCallback($params['type']);
        //创建订单
        //汇聚支付唯一标识
        $params['amount'] = $huiJuparams['q2_Amount'];
        $params['payment_nid'] = UserVipConstant::PAYMENT_TYPE_HUIJU;
        $orderOtherParams = UserVipStrategy::getUserOrderOtherParamsByParam_new($params);

        $reOrder = PaymentStrategy::getUserOrderParams_report($params, $orderOtherParams);
        logInfo("EDEEEEEEEEEEEEEEEEEEEEEEEEEEE===", $reOrder);

        $createOrder = PaymentFactory::createOrder($reOrder);

        if (!$createOrder) {

            $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
            return false;
        }
        logInfo("EDEEEEEEEEEEEEEEEEEEEEEEEEEEE");

        $back['message'] = $ret['rb_Msg'];

        //将返回结果赋值
        $this->backInfo = $back;

        return $back;
    }


    public  function http_post($url, $params,$contentType=false)
    {

        if (function_exists('curl_init')) { // curl方式
            $oCurl = curl_init();
            if (stripos($url, 'https://') !== FALSE) {
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            }
            $string = $params;
            if (is_array($params)) {
                $aPOST = array();
                foreach ($params as $key => $val) {
                    $aPOST[] = $key . '=' . urlencode($val);
                }
                $string = join('&', $aPOST);
            }
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POST, TRUE);
            //$contentType json处理
            if($contentType){
                $headers = array(
                    "Content-type: application/json;charset='utf-8'",
                );

                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($params));
            }else{
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $string);
            }
            $response = curl_exec($oCurl);
            curl_close($oCurl);
            return $response;
//        return json_decode($response, true);
        } elseif (function_exists('stream_context_create')) { // php5.3以上
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params),
                )
            );
            $_opts = stream_context_get_params(stream_context_get_default());
            $context = stream_context_create(array_merge_recursive($_opts['options'], $opts));
            return file_get_contents($url, false, $context);
//        return json_decode(file_get_contents($url, false, $context), true);
        } else {
            return FALSE;
        }
    }


    /**
     * 快捷支付 - 汇聚支付
     * 短信已经签约的卡，直接支付
     * @param array $params
     * @return bool
     *  by xuyj v3.2.3
     */
    private function quickPayment_huiju_sec($params = [])
    {
        //如果是银行卡支付
        $bankCardInfo = [];
        logInfo('quickPayment_huiju_sec', $params);
       // $subvipinfo = \GuzzleHttp\json_encode($params['subVip']);
        // 4=>汇聚
        if($params['pay_type']==4){
            $bankCardInfo = UserBankCardFactory::getBankCardInfo($params['bankcard_id'], $params['user_id']);
            if (!$bankCardInfo) {
                $this->error = ['error' => RestUtils::getErrorMessage(1135), 'code' => 1135];
                return false;
            }
            $params['bank_id'] = $bankCardInfo['bank_id'];
        }
        logInfo("&&&&&&&&&&& quickPayment_huiju_sec = ", $bankCardInfo);
        // 首次使用汇聚支付的卡
        $params = PaymentStrategy::getOrderSameSection_new($params, $bankCardInfo);
        logInfo("quickPayment_huiju  params : ", $params);
        //订单号
        $back['orderNum'] = $params['order_id'];
        //调用汇聚订单
        $otherParams = UserReportStrategy::getYibaoOtherParams_huiju();

        $huiJuparams = PaymentStrategy::orderHuiJuParams_new_Sec_report($params, $otherParams,$bankCardInfo['huijusignid']);//UserVipStrategy::orderYibaoParams($order);
        logInfo("getHuiJuOtherParamsByParam_new : ", $huiJuparams);
        if(strcmp($params['card_type'],"2")==0){
            $huiJuparams['s6_CVV2'] = $params['cvv2'];
            $huiJuparams['s5_BankCardExpire'] = $this->insertToStr($params['avatime']);//$avatime;
        }
        // 参数排序
        ksort($huiJuparams);
        //生成签名
       // $hmcVal = urlencode($this->hmacRequest($huiJuparams,"9aec0efceb804391842838bdc420ebbd"));
        $hmcVal= HuiJuUtil::i()->fetchHmacData($huiJuparams);
        $huiJuparams['hmac'] = $hmcVal;
        $ret = $this->http_post("https://www.joinpay.com/trade/agreementPayApi.action", $huiJuparams);
        $ret = \GuzzleHttp\json_decode($ret,true);
        logInfo("==== ", $ret);

        if(strcmp($ret['ra_Status'],"100")==0 || strcmp($ret['ra_Status'],"102")==0){
            logInfo("1111111111111111111");

            if (empty($ret)) {
                logInfo("22222222222222222222");

                $this->error = ['error' => RestUtils::getErrorMessage(1142), 'code' => 1142];
                return false;
            }
            //更新卡信息
            if(strcmp($params['card_type'],"2")==0){
                $bankcardinfos = array();
                $bankcardinfos['cvv2'] =  $huiJuparams['s6_CVV2'];
                $bankcardinfos['avatime'] = $huiJuparams['s5_BankCardExpire'];
                $bankcardinfos['account'] = $huiJuparams['s4_PayerBankCardNo'];
                UserBankCardFactory::updateCardInfo($bankcardinfos);
            }else if(strcmp($params['card_type'],"1")==0){
                //  UserBankCardFactory::updateCardInfo();
            }
        }else{
            $this->error = ['error' => RestUtils::getErrorMessage(1142), 'code' => 1142];
            return false;
        }
        logInfo("33333333333333333333");
        $huiJuparams['productdesc'] = "【购买报告】";
        $params['request_text'] = json_encode($huiJuparams, JSON_UNESCAPED_UNICODE);
        $params['payment_order_id'] = $ret['r3_OrderNo'];
        $params['orderId'] = $ret['r5_TrxNo'];
        $back['message'] = $ret['rb_Msg'];
        //创建订单
        //汇聚支付唯一标识
        $params['payment_nid'] = UserVipConstant::PAYMENT_TYPE_HUIJU;
        $params['amount'] = $huiJuparams['q2_Amount'];
        $orderOtherParams = UserVipStrategy::getUserOrderOtherParamsByParam_new($params);
        logInfo("@@@@@@@@@@@@ : ", $orderOtherParams);
        $subvipinfo = "no_default";
        $reOrder = PaymentStrategy::getUserOrderParams_sec($params, $orderOtherParams,$subvipinfo);
        logInfo("getUserOrderOtherParamsByParam  : ", $reOrder);
        $createOrder = PaymentFactory::createOrder($reOrder);
        if (!$createOrder) {
            $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
            return false;
        }
        //将返回结果赋值
        $this->backInfo = $back;

        return $back;
    }

}
