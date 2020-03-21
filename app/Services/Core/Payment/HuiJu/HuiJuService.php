<?php

namespace App\Services\Core\Payment\HuiJu;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Strategies\PaymentStrategy;

/**
 * 汇聚支付
 *
 * Class HuiJuService
 * @package App\Services\Core\Payment\HuiJu
 */
class HuiJuService extends PaymentService
{
    /**
     * 汇聚支付 - 下单
     * 订单支付接口
     *
     * @param array $params 参数数组 注意：参数数组中的数据类型
     * @return array|mixed|string
     */
    public function orderPay($params = [])
    {
        if (!is_array($params)) {
            return '参数必须是数组！';
        }
        //支付地址
        $url = HuiJuConfig::HUIJU_URL . '/trade/uniPayApi.action';
        //订单支付数据处理
        $data = HuiJuUtil::i()->getOrderPayParams($params);
        //订单支付数据hmac加密
        $hmacData = HuiJuUtil::i()->fetchHmacData($data);
        //签名数据
        $data['hmac'] = $hmacData;

        $request = [
            'form_params' => [
                'p0_Version' => HuiJuConfig::HUIJU_VERSION,    //目前版本号为:1.0
                'p1_MerchantNo' => HuiJuConfig::HUIJU_MERCHANTNO,   //商户编号和商户密钥在汇聚商户后台获取
                'p2_OrderNo' => $params['orderNo'], //商户系统提交的唯一订单号
                'p3_Amount' => sprintf("%.2f", $params['amount']),    //单位:元,精确到分,保留两位小数。例如:10.23
                'p4_Cur' => HuiJuConfig::HUIJU_CUR, //默认设置为 1(代表人民币)
                'p5_ProductName' => urlencode($params['productname']),  //商品名称
                'p6_ProductDesc' => urlencode($params['productdesc']),    //商品描述
                'p7_Mp' => isset($params['urlParams']) ? $params['urlParams'] : '',  //公用回传参数
           //     'p8_ReturnUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_SYN,  //汇聚支付处理完请求后,处理结果页面跳转到商户网站里指定的http 地址。
                'p9_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_ASYN,   //服务器异步通知地址
                'q1_FrpCode' => HuiJuConfig::HUIJU_FRPCODE, //交易类型
                'hmac' => urlencode($hmacData),
            ],
        ];

//        dd($request);
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

//        logInfo('orderPay',['message'=>$res,'code'=>1001]);
        //对返回结果进行分析
        if (!is_array($res)) {
            return [];
        }

        if (isset($res['ra_Code']) && $res['ra_Code'] != 100) {
            return [];
        }


        return $res;
    }

// 生成签名
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

    public function orderPay_wechat($params = [])
    {
        if (!is_array($params)) {
            return '参数必须是数组！';
        }
        //支付地址
        $url = HuiJuConfig::HUIJU_URL . '/trade/uniPayApi.action';
        //订单支付数据处理
       // $data = HuiJuUtil::i()->getOrderPayParams($params);
        logInfo("NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN", $params);
       // ksort($params);
        //$hmcVal = urlencode($this->hmacRequest($params,"9aec0efceb804391842838bdc420ebbd"));
        $data = HuiJuUtil::i()->getOrderPayParams($params);
        ksort($data);
        logInfo("JJJJJJJJJJJJJJJJJJJJJJJJ", $data);
      //  $hmcVal = urlencode($this->hmacRequest($data,"9aec0efceb804391842838bdc420ebbd"));
        $hmcVal= HuiJuUtil::i()->fetchHmacData($data);
        //订单支付数据hmac加密
      //  $hmacData = HuiJuUtil::i()->fetchHmacData($data);
        //签名数据
     //   $data['hmac'] = $hmacData;

        $request = [
            'form_params' => [
                'p0_Version' =>"1.0",    //目前版本号为:1.0
                'p1_MerchantNo' => HuiJuConfig::HUIJU_MERCHANTNO,   //商户编号和商户密钥在汇聚商户后台获取
                'p2_OrderNo' => $params['orderNo'], //商户系统提交的唯一订单号
                'p3_Amount' => sprintf("%.2f", $params['amount']),    //单位:元,精确到分,保留两位小数。例如:10.23
                'p4_Cur' => HuiJuConfig::HUIJU_CUR, //默认设置为 1(代表人民币)
                'p5_ProductName' => urlencode($params['productname']),  //商品名称
                'p6_ProductDesc' => urlencode($params['productdesc']),    //商品描述
                'p7_Mp' => isset($params['urlParams']) ? $params['urlParams'] : '',  //公用回传参数
                //     'p8_ReturnUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_SYN,  //汇聚支付处理完请求后,处理结果页面跳转到商户网站里指定的http 地址。
                'p9_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_WECHAT_ASYN,   //服务器异步通知地址
                'q1_FrpCode' => HuiJuConfig::HUIJU_FRPCODE_WECHAT, //交易类型
                'hmac' => $hmcVal,
            ],
        ];



        logInfo('request', $request);
//        dd($request);
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        logInfo("cccccccccccccccccc--", $result);
        //对返回结果进行分析
        if (!is_array($res)) {
            return [];
        }

        if (isset($res['ra_Code']) && $res['ra_Code'] != 100) {
            return [];
        }


        return $res;
    }


    // 汇聚支付
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
     * 汇聚支付 - 下单
     * 订单支付接口
     *
     * @param array $params 参数数组 注意：参数数组中的数据类型
     * @return array|mixed|string
     *  by xuyj v3.2.3
     */
    public function orderPay_new($params = [])
    {
        logInfo("1`11111111111111111111111111");
        if (!is_array($params)) {
            return '参数必须是数组！';
        }
        //支付地址
        $url = HuiJuConfig::HUIJU_URL . '/trade/agreementSmsApi.action';


//        dd($request);
        logInfo("2222222222 : ", $params);
       // $response = HttpClient::i()->request('POST', $url, $params);
        $result =$this->http_post("https://www.joinpay.com/trade/agreementSmsApi.action", $params);
    //    $result = $response->getBody()->getContents();
        logInfo("333333333333", $result);
        $res = json_decode($result, true);

//        logInfo('orderPay',['message'=>$res,'code'=>1001]);
        //对返回结果进行分析
        if (!is_array($res)) {
            return [];
        }

        if (isset($res['ra_Code']) && $res['ra_Code'] != 100) {
            return [];
        }

        logInfo("66666666666666666666");
        return $res;
    }

}