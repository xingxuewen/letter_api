<?php

namespace App\Http\Controllers\V1;

use App\Constants\PaymentConstant;
use App\Helpers\Logger\SLogger;
use App\Http\Controllers\Controller;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Services\Core\Payment\HuiJu\HuiJuUtil;
use App\Services\Core\Payment\YiBao\YiBaoService;
use App\Strategies\PaymentStrategy;
use Illuminate\Http\Request;
use App\Helpers\RestResponseFactory;

/**
 * 易宝回调
 *
 * Class PaymentCallbackController
 * @package App\Http\Controllers\V1
 */
class PaymentCallbackController extends Controller
{
    /**
     * 易宝同步回调
     *
     * @param Request $request
     * @return string
     */
    public function yiBaoSynCallBack(Request $request)
    {
        logInfo('易宝同步回调', ['code' => 10300]);
        $params = $request->all();
        if (!isset($params['type']) || !isset($params['data']) || !isset($params['encryptkey'])) {
            return 'ERROR';
        }
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($return['orderid']);

        if ($status == 1) {
            return 'SUCCESS';
        } else {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if (isset($result['error'])) {
                return $result;
            }

            return 'SUCCESS';
        }
    }

    /**
     * 易宝异步回调
     *
     * @param Request $request
     * @return string
     */
    public function yiBaoAsynCallBack(Request $request)
    {
        logInfo('易宝异步回调', ['code' => 10200]);
        $params = $request->all();
        if (!isset($params['type']) || !isset($params['data']) || !isset($params['encryptkey'])) {
            return 'ERROR';
        }
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($return['orderid']);
        if ($status == 1) {
            return 'SUCCESS';
        } else {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if (isset($result['error'])) {
                return $result;
            }

            return 'SUCCESS';
        }
    }

    /**
     * 汇聚同步回调
     *
     * @param Request $request
     * @return string
     */
    public function huiJuSynCallBack(Request $request)
    {
        logInfo('汇聚同步回调', ['code' => 10300]);
        $params = $request->all();

        // hmac 签名数据 ; r1_MerchantNo 商户编号 ; r5_Mp 公用回传参数
        if (!isset($params['hmac']) || !isset($params['r1_MerchantNo']) || !isset($params['r5_Mp'])) {
            return 'error';
        }

        //验证汇聚支付hmac签名
        $params = HuiJuUtil::i()->urldecodeParams($params);
        $return = HuiJuUtil::i()->undoHmacData($params);

        if (!$return) //签名验证失败
        {
            return 'error';
        }

        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($params['r2_OrderNo']);
        //对公用回传参数进行处理
        $params = PaymentStrategy::getHuijuCallbackParams($params);
        if (empty($params['type']) || empty($params['vip_type'])) {
            return 'error';
        }

        //回调支付渠道定义
        $params['pay_nid'] = PaymentConstant::PAYMENT_HUIJU_ALIPAY_NID;

        if ($status == 1) //成功
        {
            return 'success';
        } else //回调逻辑处理
        {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if (isset($result['error'])) {
                return $result;
            }

            return 'success';
        }
    }

    /**
     * 汇聚异步回调
     *
     * @param Request $request
     * @return string
     */
    public function huiJuAsynCallBack(Request $request)
    {
        logInfo('汇聚异步回调', ['code' => 10300]);
        $params = $request->all();

        // hmac 签名数据 ; r1_MerchantNo 商户编号 ; r5_Mp 公用回传参数
        if (!isset($params['hmac']) || !isset($params['r1_MerchantNo']) || !isset($params['r5_Mp'])) {
            return 'error';
        }

        //验证汇聚支付hmac签名
        $params = HuiJuUtil::i()->urldecodeParams($params);
        $return = HuiJuUtil::i()->undoHmacData($params);

        if (!$return) //签名验证失败
        {
            return 'error';
        }

        //对订单状态进行判断
        $status = PaymentFactory::getOrderStatus($params['r2_OrderNo']);
        //对公用回传参数进行处理
        $params = PaymentStrategy::getHuijuCallbackParams($params);

        if (empty($params['type'])) {
            return 'error';
        }

        //回调支付渠道定义
        $params['pay_nid'] = PaymentConstant::PAYMENT_HUIJU_ALIPAY_NID;

        if ($status == 1) //成功
        {
            return 'success';
        } else //回调逻辑处理
        {
            $result = PaymentStrategy::getDiffOrderChain($params);
            if (isset($result['error'])) {
                return $result;
            }

            return 'success';
        }
    }

    /**
     * 汇聚异步回调
     *
     * @param Request $request
     * @return string
     *  by xuyj v3.2.3
     */
    public function asyncallbacks_wechat(Request $request)
    {
        logInfo('汇聚异步回调---WECHAT', ['code' => 10300]);
        $params = $request->all();

        logInfo("汇聚异步回调2222222 : ", $params);
        if (!isset($params['hmac']) || !isset($params['r1_MerchantNo']) || !isset($params['r5_Mp'])) {
            return 'error';
        }
        $status = PaymentFactory::getOrderStatus_wechat($params['r2_OrderNo']);
        //对公用回传参数进行处理
        $params = PaymentStrategy::getHuijuCallbackParams($params);

        if (empty($params['type'])) {
            return 'error';
        }

        //回调支付渠道定义
        $params['pay_nid'] = PaymentConstant::PAYMENT_HUIJU_ALIPAY_NID;

        if ($status == 1) //成功
        {
            return 'success';
        } else //回调逻辑处理
        {
            logInfo("汇聚异步回调iiiiiiiiiiiiiiiiiiiiiii ---", $params);
            $result = PaymentStrategy::getDiffOrderChain_wechat($params);
            if (isset($result['error'])) {
                return $result;
            }

            return 'success';
        }

    }
    /**
     * 汇聚异步回调
     *
     * @param Request $request
     * @return string
     *  by xuyj v3.2.3
     */
    public function huiJuAsynCallBack_quick(Request $request)
    {
        $params = $request->all();
        logInfo("汇聚异步回调2222222 : ", $params);
        if(strcmp($params['r6_Status'],"100")==0 || strcmp($params['r6_Status'],"102")==0 ){
            logInfo('汇聚异步回调验证通过 ');
        }else{
            return 'error';
        }
     /*   // hmac 签名数据 ; r1_MerchantNo 商户编号 ; r5_Mp 公用回传参数
        if (!isset($params['hmac']) || !isset($params['r1_MerchantNo']) || !isset($params['r5_Mp'])) {
            return 'error';
        }

        //验证汇聚支付hmac签名
        $params = HuiJuUtil::i()->urldecodeParams($params);
        $return = HuiJuUtil::i()->undoHmacData($params);

        if (!$return) //签名验证失败
        {
            return 'error';
        }
*/
        //对订单状态进行判断
        logInfo("3333333 : ", $params['r2_OrderNo']);
        $status = PaymentFactory::getOrderStatus_new($params['r2_OrderNo']);
        logInfo("bbbbbbbbbb---", ['status' => $status]);
        //对公用回传参数进行处理
      //  $params = PaymentStrategy::getHuijuCallbackParams($params);

        if (empty($status['subtype'])) {
            return 'error';
        }
     //   $res = \GuzzleHttp\json_decode($status['subtype']);
        if(strcmp($status['subtype'],"no_default")!=0){
            $res = \Qiniu\json_decode($status['subtype'],true);
            $params['viptype'] = $res['id'];
            $resTypeid = PaymentFactory::getOrderStatus_report($status['order_type']);
            $params['type']=$resTypeid['type_nid'];
        }else{
            $resTypeid = PaymentFactory::getOrderStatus_report($status['order_type']);
            if(strcmp($resTypeid['type_nid'],"user_report")==0){
                $params['viptype'] ="100";
            }
            $params['type']=$resTypeid['type_nid'];

        }

   //     logInfo("HHHHHHHHHHHH----".$resTypeid['type_nid']);

        //回调支付渠道定义
        $params['pay_nid'] = PaymentConstant::PAYMENT_HUIJU_ALIPAY_NID;
        if ($status['status'] == 1) //成功
        {
            return 'success';
        } else //回调逻辑处理
        {

            //   if(isset($params['viptype']) && is_numeric($params['viptype'])){
                logInfo("BBBBBBBBBBB");
                $result = PaymentStrategy::getDiffOrderChain_new($params);

        //    }else{
           //     $result = PaymentStrategy::getDiffOrderChain($params);
        //    }
            if (isset($result['error'])) {
                return $result;
            }
            $resCLS = PaymentStrategy::updateLastCardStatusByOrderid($params['r2_OrderNo']);

            // 设置上次支付卡
            return 'success';
        }
    }

    /**
     * 判断当前卡的类型
     *
     * @param Request $request
     * @return string
     *  by xuyj v3.2.3
     */
    public function cardtype(Request $request)
    {
        $params = $request->all();
        $res = file_get_contents("https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardBinCheck=true&cardNo=" . $params['cardnum']);
        $result = \GuzzleHttp\json_decode($res,true);
        logInfo("121111111111111111111111111=", $result['validated']);

        if (!empty($result) && intval($result['validated'])!=0) {

            $resBankInfo = PaymentStrategy::getBanknameByabr($result['bank']);
            if (strcmp($result['cardType'], "DC") == 0 || strcmp($result['cardType'], "PC") == 0) {
                // 储蓄卡
                logInfo("222222222222222222");
                if(!empty($resBankInfo)){
                    $bankdata['bankname'] = $resBankInfo['bankname'];
                    $bankdata['bankid'] = $resBankInfo['bankid'];
                }else{
                    $bankdata['bankname'] = $result['bank'];
                    $bankdata['bankid'] = "0";
                }
                $isBang = UserBankCardFactory::isBankCardBang_cxk($params['cardnum']);
                if(empty($isBang)){
                    $bankdata['isBang'] = "0";
                }else{
                    $bankdata['isBang'] = "1";
                }
                $bankdata['cardtype'] = "1";
                return RestResponseFactory::ok($bankdata);
            }else if(strcmp($result['cardType'], "CC") == 0 || strcmp($result['cardType'], "SCC") == 0){
                    // 信用卡
                if(!empty($resBankInfo)){
                    $bankdata['bankname'] = $resBankInfo['bankname'];
                    $bankdata['bankid'] = $resBankInfo['bankid'];
                }else{
                    $bankdata['bankname'] = $result['bank'];
                    $bankdata['bankid'] = "0";
                }
                $isBang = UserBankCardFactory::isBankCardBang_creditcard($params['cardnum']);
                if(empty($isBang)){
                    $bankdata['isBang'] = "0";
                }else{
                    $bankdata['isBang'] = "1";
                }
                $bankdata['cardtype'] = "2";
                return RestResponseFactory::ok($bankdata);
            }else{
                return RestResponseFactory::ok("","10086","10087","请检查卡号是否正确");

            }
        }else{
            logInfo("yyyyyyyyy");
            return RestResponseFactory::ok("","10086","10087","请检查卡号是否正确");
        }
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

    // 用订单号去汇聚支付平台查询 该订单状态
    // v3.2.3 by xuyj
    public function checkorderstatus(Request $request){
        $order['orderid'] = $request->input('orderid', '0');

        $params = array();
        $params['p1_MerchantNo'] = "888105100000721";
        if(strcmp($order['orderid'],"0")!="0"){
            $params['p2_OrderNo'] = $order['orderid'];
        }else{
            return RestResponseFactory::ok("","10086","10087","订单正在查询中");
        }
        ksort($params);
        $hmcVal= HuiJuUtil::i()->fetchHmacData($params);
        $params['hmac'] = $hmcVal;
        $ret = $this->http_post("https://www.joinpay.com/trade/queryOrder.action",$params);
        logInfo("1111111111111111111----", $ret);
        $ret = \GuzzleHttp\json_decode($ret,true);
        if(strcmp($ret['ra_Status'],"100")==0 || strcmp($ret['ra_Status'],"102")==0){
            $resp = array();
            $resp['message'] = "订单支付成功";
            return RestResponseFactory::ok($resp);
        }else{
            return RestResponseFactory::ok("","010087","10087","订单支付不成功");
        }

    }

    /**
     * 返回汇聚支持支付的银行名
     *
     * @param Request $request
     * @return string
     *  by xuyj v3.2.3
     */
    public function surportBank(Request $request)
    {
        $resBankInfo = PaymentStrategy::fetchHuiJuPaySuportBank();
        logInfo('surportBank', $resBankInfo);
        if(!empty($resBankInfo)){
            return RestResponseFactory::ok($resBankInfo);
        }else{
            return RestResponseFactory::ok("","10086","10087","暂无支持银行.");
        }


    }
}