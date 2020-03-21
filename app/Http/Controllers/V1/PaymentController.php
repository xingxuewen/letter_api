<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-10-26
 * Time: 上午11:43
 */

namespace App\Http\Controllers\V1;

use App\Constants\PaymentConstant;
use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Order\VipOrder\DoVipOrderLogicHandler;
use App\Models\Factory\Admin\User\UserRealNameFactory;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\huijubanksType;
use App\Models\Orm\UserRealName;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserBankCardStrategy;
use App\Strategies\UserReportStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;
use App\Services\Core\Payment\HuiJu\HuiJuUtil;

/**
 * 支付
 *
 * Class PaymentController
 * @package App\Http\Controllers\V1
 */
class PaymentController extends Controller
{
    /**
     * 确认页面,商品信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrderInfo(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $orderType = $request->input('type', '');
        $id = $request->input('userBankId', '');

        $data = [];
        //存在银行id，按银行id进行查询
        if ($id) {
            $reqData['userId'] = $userId;
            $reqData['id'] = $id;
            $data = UserBankCardFactory::fetchUserBankInfoById($reqData);
        } else {
            //查询没有上次支付状态则设置一个默认支付状态
            $data = UserBankCardFactory::fetchCardLastPayById($userId);
        }
        //没有上次支付则使用默认或是最近添加银行卡
        if (!$data) {
            //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
            $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($userId);
            //再次查询新设置的支付卡
            $data = UserBankCardFactory::fetchCardLastPayById($userId);
        }

        //数据处理
        $data = UserBankCardStrategy::getPaymentBank($data);
        $data['orderType'] = $orderType;
        //根据类型获取id
        //是否是会员
        $userVipType = UserVipFactory::fetchUserVipToTypeByUserId($userId);
        //根据vipType 获取vip_nid
        $data['vipNid'] = UserVipFactory::fetchVipTypeById($userVipType);

        //会员信息
        $data['message'] = UserVipFactory::getVIPInfo($userId, $userVipType);
        //支付宝支付开关
        $pays['type_nid'] = PaymentConstant::PAYMENT_TYPE_ALIPAY_NID;
        $pays['nid'] = PaymentConstant::PAYMENT_HUIJU_ALIPAY_NID;
        $accountPay = PaymentFactory::fetchAccountPayment($pays);
        $datas['alipay_status'] = empty($accountPay) ? 0 : PaymentConstant::PAYMENT_HUIJU_ALIPAY_STATUS;

        //数据处理
        $reList = PaymentStrategy::getDiffTypeInfo($data, $datas);
        $reList['default_card'] = $data;

        return RestResponseFactory::ok($reList);
    }



    /**
     * 确认页面,商品信息 - 新 v3.2.3
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *  by xuyj 2019-02-15  change 2019-02-21
     */
    public function fetchOrderInfo_new(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $orderType = $request->input('type', '');
        $id = $request->input('userBankId', '');
        logInfo("1231231234123123");
        $data = [];
        //存在银行id，按银行id进行查询
        if ($id) {
            $reqData['userId'] = $userId;
            $reqData['id'] = $id;
            $data = UserBankCardFactory::fetchUserBankInfoById_new($reqData);
        } else {
            //查询没有上次支付状态则设置一个默认支付状态
            $data = UserBankCardFactory::fetchCardLastPayById_new($userId);
        }
        logInfo("6666666666666666", $data);
        //没有上次支付则使用默认或是最近添加银行卡
        if (!$data) {
            //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
            $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($userId);
            if(empty($cardLastPay)){
            }else{
                $data = UserBankCardFactory::fetchCardLastPayById($userId);
            }
            //再次查询新设置的支付卡

        }
      //  logInfo("");
        //数据处理
        if(!empty($data)){
            $data = UserBankCardStrategy::getPaymentBank_new($data,$userId);
        }
     //   $data = UserBankCardStrategy::getPaymentBank_new($data);
        $data['orderType'] = $orderType;
      //  logInfo("fetchOrderInfo_new line 134 ==== ".$data['orderType']);
        //根据类型获取id
        //是否是会员
        $userVipType = UserVipFactory::fetchUserVipToTypeByUserId($userId);
        //根据vipType 获取vip_nid
        $data['vipNid'] = UserVipFactory::fetchVipTypeById($userVipType);

        //会员信息
        $data['message'] = UserVipFactory::getVIPInfo($userId, $userVipType);
        //微信支付开关
        $pays['type_nid'] = PaymentConstant::PAYMENT_TYPE_WECHAT_XCX_PAY_NID;
        // $pays['nid'] = PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_NID;
        $pays['nid'] = PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_NID_NEW;

        $accountPay = PaymentFactory::fetchAccountPayment_new($pays);
        $datas['alipay_status'] ='0';// empty($accountPay) ? 0 : PaymentConstant::PAYMENT_HUIJU_ALIPAY_STATUS; by xuyj
        $datas['wechat'] = empty($accountPay) ? 0 :intval($accountPay['is_wechat_pay'])>0?$accountPay['is_wechat_pay']:0;// PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_STATUS;  // by xuyj
     //   $datas[]
        //数据处理
        $reList = PaymentStrategy::getDiffTypeInfo_new($data, $datas);


        if(isset($data['cvv2'])){
            if(strcmp($data['cvv2'],"0")==0){
                $data['cvv2']="";
            }
        }else{

        }

        logInfo("hhhhhhhhhhhhhhhhhhh");
        if(isset($data['avatime'])){
            if(strcmp($data['avatime'],"0")==0){
                $data['avatime']="";
            }
        }

        $reList['default_card'] = $data;

        if(!empty($data['bank_name'])){
            $resBk = UserBankCardFactory::fetchCurCardisInHuiJu($data['bank_name']);
        }

        if(!empty($resBk)){
            $reList['isHuiJuSup'] = "1";
        }else{
            $reList['isHuiJuSup'] = "0";
        }
        if(strcmp($accountPay['ishuijupay'],"1")==0){
            $reList['payChanl'] = "huiju";
        }else if(strcmp($accountPay['ishuijupay'],"0")==0){
            $reList['payChanl'] = "yibao";
        }
        $resRn = UserBankCardFactory::fetchIsRealnameOK($userId);
        if(empty($resRn)){
            $reList['realnameType'] = "0";
        }else {
            if(intval($resRn['status'])>2){
                $reList['realnameType'] = "1";
            }else{
                $reList['realnameType'] = "0";
            }
        }
        return RestResponseFactory::ok($reList);
    }

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
        $order['userId'] = $request->user()->sd_user_id;
        $order['type'] = $request->input('type');
        //实名认证状态值
        $order['realnameType'] = $request->input('realnameType', '');

        $result = PaymentStrategy::getDiffOrderTypeChain($order);
        if (isset($result['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $result['error'], $result['code']);
        }

        return RestResponseFactory::ok($result);
    }

    /**
     * 订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrder_new(Request $request)
    {
        logInfo("wwwwwwwwwwwwwwwwwww--", $request->all());

        $order['pay_type'] = (int)$request->input('payType', 3);
        $order['terminal_id'] = $request->input('terminalId', '');
        $order['bankcard_id'] = (int)$request->input('bankcardId', 0); //user_banks表的ID
        $order['shadow_nid'] = $request->input('shadowNid', '');
        $order['user_id'] = (string)$request->user()->sd_user_id;
        $order['userId'] = $request->user()->sd_user_id;
        $order['type'] = $request->input('type');
        //实名认证状态值
        $order['realnameType'] = $request->input('realnameType', '');
      //  $newcardinfo = array();
        $order['isBangCard'] = $request->input('isBangCard', '0');  // 是否新绑卡 1新绑卡 0 不绑卡
        $order['name'] = $request->input('name', '0');
        $order['idcard'] = $request->input('idcard', '0');
        $order['bankcard'] = $request->input('bankcard', '0');
        $order['mobile'] = $request->input('mobile', '0');
        $order['bankname'] = $request->input('bankname', '0');
        $order['cardtype']= $request->input('cardtype', '0');
        $order['bankid'] = $request->input('bankid', '1');
        $order['cvv2'] = $request->input('cvv2', '0');
        $order['avatime'] = $request->input('avatime', '0');
        if(strcmp($order['isBangCard'],"0")==0 && strcmp($order['pay_type'],"4")==0){
            if(strcmp($order['bankcard_id'],"0")==0){
                return RestResponseFactory::ok(RestUtils::getStdObj(),"1141","1141","交易失败,请检查信息或联系客服");
            }else{
                $res = UserBankCardFactory::fetchUserbanks_isAva($order['bankcard_id'],$order['user_id']);
                if(strcmp($res ,"0")==0){
                    return RestResponseFactory::ok(RestUtils::getStdObj(),"1141","1141","交易失败,请检查信息或联系客服");
                }
            }
        }
        $result = PaymentStrategy::getDiffOrderTypeChain_new($order);
        if (isset($result['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $result['error'], $result['code']);
        }

        return RestResponseFactory::ok($result);
    }


    /**
     *  判断当前使用哪种支付通道和逻辑
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *  by xuyj v3.2.3 2019-02-22
     */
    public function choicepaychannel(Request $request)
    {
        $paychanel = PaymentFactory::fetchPaymentChalChoice();
        logInfo("zzzzzzzzzzzzzzzzzzzzzzz---", $paychanel['ishuijupay']);
   //     $datas['wechat'] = empty($accountPay) ? 0 : PaymentConstant::PAYMENT_HUIJU_WECHAT_XCX_PAY_STATUS;  // by xuyj
        if(empty($paychanel)){
            $datas['paychanel']="0";
            $datas['wechatpay'] = "0";
        }else{
            $datas['paychanel'] = $paychanel['ishuijupay'];
            $datas['wechatpay'] = $paychanel['is_wechat_pay'];

        }
   //     $datas['alipay'] ="0";
        return RestResponseFactory::ok($datas);
    }

    private  function hmacRequest($params, $key, $encryptType = "1")
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
    /**
     * 汇聚短信签约
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *  by xuyj v3.2.3
     */
    public function huiJuSign(Request $request){
        $order['orderid'] = $request->input('orderid', 3);
        $order['terminal_id'] = $request->input('terminalId', '');
        $order['payType'] = $request->input('payType', '4');
        $order['smscode'] =  $request->input('smscode', '0');
        $order['realname'] = $request->input('realname', '0');
        $order['idcard'] = $request->input('idcard', '0');
        $order['mobile'] = $request->input('mobile', '0');
        $order['cardtype'] = $request->input('cardtype', '1');
        $order['cvv2'] = $request->input('cvv2', '0');
        $order['avatime'] = $request->input('avatime', '0');
        $order['isBangCard'] = $request->input('isBangCard', '0');  // 是否新绑卡 1新绑卡 0 不绑卡
        $order['bankname'] = $request->input('bankname', '0');
        $order['bankid'] = $request->input('bankid', '1');
        $order['cvv2'] = $request->input('cvv2', '0');
        $order['avatime'] = $request->input('avatime', '0');
        $bankcard =  $request->input('bankcard', '1');
        $createOrder = PaymentFactory::fetchOrderInfoByOrderId($order['orderid'],(string)$request->user()->sd_user_id);
        if (empty($createOrder)) {
            $result = ['error' => RestUtils::getErrorMessage(1141), 'code' => 1141];
            return RestResponseFactory::ok(RestUtils::getStdObj(),$result['error'] , $result['code']);
        }
        logInfo("KKKKKKKKKHHHHHHHHHHH----", $createOrder);
        if( strcmp($createOrder['card_num'],$bankcard)!=0){
            return RestResponseFactory::ok(RestUtils::getStdObj(),"1141","1141","交易失败，请检查信息或联系客服");
        }


        $huiJuParams = array();
        $huiJuParams['p0_Version'] ="2.0";
        $huiJuParams['p1_MerchantNo']="888105100000721";
        $huiJuParams['p2_MerchantName'] = "速贷之家";
        $huiJuParams['q1_OrderNo'] = $order['orderid'];
        $huiJuParams['q3_Cur'] ="1";
        $huiJuParams['q8_FrpCode'] ="FAST";
        $huiJuParams['s1_PayerName'] = $order['realname'];
        $huiJuParams['s2_PayerCardType'] ="1";
        $huiJuParams['s3_PayerCardNo'] =$order['idcard'];
        $huiJuParams['s4_PayerBankCardNo'] = $createOrder['card_num'];
        $huiJuParams['s7_BankMobile'] = $order['mobile'];
        $huiJuParams['q2_Amount'] = $createOrder['amount'];
        $huiJuParams['t2_SmsCode'] =$order['smscode'];
        if(strcmp($order['cardtype'],"2")==0){
            $huiJuParams['s6_CVV2'] = $order['cvv2'];
            $huiJuParams['s5_BankCardExpire'] = $this->insertToStr($order['avatime']);//$resUu['avatime'];
        }

        ksort($huiJuParams);
        $hmcVal= HuiJuUtil::i()->fetchHmacData($huiJuParams);
        $huiJuParams['hmac'] = $hmcVal;
        $result =$this->http_post("https://www.joinpay.com/trade/agreementSignApi.action", $huiJuParams);
        logInfo("KKKKKKKKKHHHHHHHHHHH: ", $result);

        $resArr = \GuzzleHttp\json_decode($result,true);

        if(strcmp($resArr['ra_Status'],"100")!=0){
            if(strcmp($resArr['rb_Msg'],"该流水号交易已存在")==0 || strcmp($resArr['ra_Status'],"EB000014")==0 || strcmp($resArr['rb_Msg'],"交易失败[系统异常]")==0 ){
                return RestResponseFactory::ok(RestUtils::getStdObj(),"9998","9998","请重新获取验证码");
            }

            $errorMsg = PaymentFactory::getErrorMsg($resArr['ra_Status'], '交易失败，请检查信息或联系客服~');
            return RestResponseFactory::ok(RestUtils::getStdObj(),"1141","1141", $errorMsg);
        }

        if(strlen($resArr['signedId'])>=10 && strcmp($resArr['ra_Status'],"100")==0){
            if(strcmp($order['isBangCard'],"1")==0){
                //添加卡片
                logInfo(";;;;;;;;;;;;;;;");
                $addcardparam = array();
                $addcardparam['account'] = $huiJuParams['s4_PayerBankCardNo'];
                $addcardparam['userId'] = (string)$request->user()->sd_user_id;
                $addcardparam['bankId'] = $order['bankid'];
                $addcardparam['bankname'] = $order['bankname'];
                $addcardparam['cardtype'] = $order['cardtype'];
                if(strcmp($order['cardtype'],"2")==0){
                    $addcardparam['card_default'] ="0";// UserBankCardFactory::fetchUserbanks_isDefault($addcardparam['userId']);
                }else if(strcmp($order['cardtype'],"1")==0){
                    $addcardparam['card_default'] = UserBankCardFactory::fetchUserbanks_isDefault($addcardparam['userId']);
                }else{
                    $addcardparam['card_default'] = "1";
                }

                $addcardparam['card_last_status'] = "0";
                $addcardparam['mobile'] = $order['mobile'];
                if(strcmp($order['cardtype'],"2")==0){
                    $addcardparam['cvv2'] = $huiJuParams['s6_CVV2'] ;
                    $addcardparam['avatime'] = $huiJuParams['s5_BankCardExpire'];
                }else{
                    $addcardparam['cvv2'] = "0" ;
                    $addcardparam['avatime'] = "0";
                }
                logInfo(";;;;;;;;;;;;;;;;;", $addcardparam);
                UserBankCardFactory::createOrUpdateUserBank_new_bangcard($addcardparam);
                UserBankCardFactory::insertRealNameByBangcard($huiJuParams['s1_PayerName'],$huiJuParams['s3_PayerCardNo'],$addcardparam['userId']);
            }else{
                if(strcmp($order['cardtype'],"2")==0){
                    $bankcardinfos = array();
                    $bankcardinfos['cvv2'] =  $huiJuParams['s6_CVV2'];
                    $bankcardinfos['avatime'] = $huiJuParams['s5_BankCardExpire'];
                    $bankcardinfos['account'] = $huiJuParams['s4_PayerBankCardNo'];
                    UserBankCardFactory::updateCardInfo($bankcardinfos);
                }else if(strcmp($order['cardtype'],"1")==0){
                    //  UserBankCardFactory::updateCardInfo();
                }
            }

            logInfo("pppppppppppppppppppppppppppp");

            $param_pay = array(
                'p0_Version' =>"2.0",
                'p1_MerchantNo'=>"888105100000721",
                'p2_MerchantName' => "速贷之家",
                'q1_OrderNo' =>$order['orderid'],
                'q2_Amount' =>$huiJuParams['q2_Amount'],
                'q3_Cur' =>"1",
                'q4_ProductName' =>"充值会员",
                'q7_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_QUICK_ASYN,
                'q8_FrpCode' =>"FAST",
                's1_PayerName' =>$huiJuParams['s1_PayerName'],
                's2_PayerCardType' =>"1",
                's3_PayerCardNo' =>$huiJuParams['s3_PayerCardNo'],
                's4_PayerBankCardNo' =>$huiJuParams['s4_PayerBankCardNo'],
                's7_BankMobile' =>$huiJuParams['s7_BankMobile'],
                's9_bankSignId' =>$resArr['signedId'],
            );
            if(strcmp($order['cardtype'],"2")==0){
                $param_pay['s6_CVV2'] = $order['cvv2'];
                $param_pay['s5_BankCardExpire'] = $this->insertToStr($order['avatime']);//$resUu['avatime'];
            }
            logInfo("1111111111111111111111111111111111");
            ksort($param_pay);
           // $hmcVal = urlencode($this->hmacRequest($param_pay,"9aec0efceb804391842838bdc420ebbd"));
            $hmcVal= HuiJuUtil::i()->fetchHmacData($param_pay);
            $param_pay['hmac'] = $hmcVal;
            $result_pay =$this->http_post("https://www.joinpay.com/trade/agreementPayApi.action", $param_pay);
            $resPar_pay = json_decode($result_pay,true);
            logInfo("333333333333 = ", $result_pay);
            if(strcmp($resPar_pay['ra_Status'],"100")==0 || strcmp($resPar_pay['ra_Status'],"102")==0){
                $createOrder = UserBankCardFactory::updateHuiJuPaySmsSignCodeBySignCode((string)$request->user()->sd_user_id,$huiJuParams['s4_PayerBankCardNo'],$huiJuParams['s7_BankMobile'],$resArr['signedId']);
                PaymentStrategy::updateLastCardStatusByOrderid($order['orderid']);
                return RestResponseFactory::ok(['message'=>"交易成功"]);
            }else{
              //  return RestResponseFactory::error(['message'=>"交易失败! 请检查信息或联系客服"],1,$resArr['rb_Msg']);
                return RestResponseFactory::ok(RestUtils::getStdObj(),"9992","9992","交易失败,请检查信息或联系客服");
            }
        }
      //  logInfo("343434343 = ".$result);
    }


    /**
     * 根据订单号查询订单是否支付成功
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPaymentStatus(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['orderNum'] = $request->input('orderNum', '');

        //付费进行中 不可以填写授权
        $data['payType'] = UserReportConstant::PAY_TYPE_PAY;
        $payReport = UserReportFactory::fetchReportingByIdAndType($data);
        //报告订单生成状态
        $res['report_status'] = 0;
        if ($payReport && $payReport['step'] == 0) {
            $res['report_status'] = 1;
        }

        //vip状态
        $vip = UserVipFactory::getVIPInfoByUserId($data['userId']);
        $res['vip_status'] = 0;
        if ($vip) {
            $res['vip_status'] = $vip['status'];
        }

        //根据订单号查看订单是否支付成功
        $res['order_status'] = PaymentFactory::fetchPaymentStatusByOrderId($data);

        return RestResponseFactory::ok($res);
    }

    /**
     * 根据订单号查询订单是否支付成功---汇聚支付
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPaymentStatus_new(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['orderNum'] = $request->input('orderNum', '');

        //付费进行中 不可以填写授权
        $data['payType'] = UserReportConstant::PAY_TYPE_PAY;
        $payReport = UserReportFactory::fetchReportingByIdAndType($data);
        //报告订单生成状态
        $res['report_status'] = 0;
        if ($payReport && $payReport['step'] == 0) {
            $res['report_status'] = 1;
        }

        //vip状态
        $vip = UserVipFactory::getVIPInfoByUserId($data['userId']);
        $res['vip_status'] = 0;
        if ($vip) {
            $res['vip_status'] = $vip['status'];
        }

        //根据订单号查看订单是否支付成功
        $res['order_status'] = $this->checkorderstatus_new($data['orderNum']);//PaymentFactory::fetchPaymentStatusByOrderId($data);

        return RestResponseFactory::ok($res);
    }

    private function checkorderstatus_new($orderid){

        $params = array();
        $params['p1_MerchantNo'] = "888105100000721";
        if(strcmp($orderid,"")!="0"){
            $params['p2_OrderNo'] = $orderid;
        }else{
         //   return RestResponseFactory::ok("","10086","10087","订单正在查询中");
            return "0";
        }
        ksort($params);
        $hmcVal= HuiJuUtil::i()->fetchHmacData($params);
        $params['hmac'] = $hmcVal;
        $ret = $this->http_post("https://www.joinpay.com/trade/queryOrder.action",$params);
        logInfo("RRRRRRRRRRRRRRR----", $ret);
        $ret = \GuzzleHttp\json_decode($ret,true);
        if(strcmp($ret['ra_Status'],"100")==0 ){
            return "1";
        }else{
           return "0";
        }

    }


}