<?php

namespace App\Services\Core\Payment\HuiJu;

use App\Helpers\Logger\SLogger;
use App\Models\Factory\PaymentFactory;
use App\Services\AppService;

/**
 * 汇聚支付数据处理
 *
 * Class HuiJuUtil
 * @package App\Services\Core\Payment\HuiJu
 */
class HuiJuUtil
{
    private $sd_private_key;   //速贷私钥
    private $sd_public_key;    //速贷公钥
    private $hj_public_key;     //汇聚公钥
    public static $util;
    public static $nid;    //支付类型唯一标识

    /**
     * 初始调用
     *
     * @param string $nid
     * @return static
     */
    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }

        return self::$util;
    }

    /**
     * 自动加载秘钥
     *
     * HuiJuUtil constructor.
     * @param string $nid
     */
    public function __construct($nid = 'HJZF')
    {
        self::$nid = $nid;
        $this->sd_private_key = PaymentFactory::getYibaoMerchantPrivateKey(self::$nid);
        $this->sd_public_key = PaymentFactory::getYibaoMerchantPublicKey(self::$nid);
        $this->hj_public_key = PaymentFactory::getYibaoPublicKey(self::$nid);
    }

    /**
     * 订单支付参数
     *
     * @param array $params
     * @return array
     */
    public function getOrderPayParams($params = [])
    {
        $request = [
            'p0_Version' => HuiJuConfig::HUIJU_VERSION,    //目前版本号为:1.0
            'p1_MerchantNo' => HuiJuConfig::HUIJU_MERCHANTNO,   //商户编号和商户密钥在汇聚商户后台获取
            'p2_OrderNo' => $params['orderNo'], //商户系统提交的唯一订单号
            'p3_Amount' => $params['amount'],    //单位:元,精确到分,保留两位小数。例如:10.23
            'p4_Cur' => HuiJuConfig::HUIJU_CUR, //默认设置为 1(代表人民币)
            'p5_ProductName' => $params['productname'],  //商品名称
            'p6_ProductDesc' => $params['productdesc'],    //商品描述
            'p7_Mp' => isset($params['urlParams']) ? $params['urlParams'] : '',  //公用回传参数
       //     'p8_ReturnUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_SYN,  //汇聚支付处理完请求后,处理结果页面跳转到商户网站里指定的http 地址。
            'p9_NotifyUrl' => AppService::HUIJU_CALLBACK_URL . AppService::API_URL_HUIJU_WECHAT_ASYN,   //服务器异步通知地址
            'q1_FrpCode' => HuiJuConfig::HUIJU_FRPCODE_WECHAT, //交易类型
        ];

        return $request;
    }

    /*
     * ---------------------------------------
     *
     * 无论是请求还是应答,都按照以下方式拼接待签名字符串:
     * 1、除 hmac 字段外,所有参数按照文档要求的顺序设值,并参与拼接待签
     * 名字符串。
     * 2、在待签名字符串中,字段名和字段值都采用原始值,不进行 URL Encode。
     *
     * -----------------------------------------
     */
    public function fetchHmacData($params = [])
    {
        //拼接参数
        $params = implode("", $params);
        //加签算法
        $encryptRes = self::encryptSignBySdRsaPrivate($params);

        //解密 验证公钥可用性
//        $res = self::voidSignBySdRsaPublic($params, $encryptRes);
//        dd($res);

        return $encryptRes;
    }

    /**
     * 汇聚支付 - 公钥验证签名
     *
     * @param array $params
     * @return string
     */
    public function undoHmacData($params = [])
    {
        $encryptRes = urldecode($params['hmac']);
        unset($params['hmac']);

        //拼接字符串
        $params = implode('', $params);

        //urldecode加密参数
        return self::voidSignByHjRsaPublic($params, $encryptRes);
    }

    /**
     * 速贷 - 公钥验证签名
     *
     * @param string $param
     * @return string
     */
    public function voidSignByHjRsaPublic($param = '', $encryptRes = '')
    {
        //公钥内容
        $public_content = $this->hj_public_key;
        //处理公钥
        $str = chunk_split($public_content, 64, "\n");
        $public_content = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";

        $res = openssl_get_publickey($public_content);
//        dd($res);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($param, base64_decode($encryptRes), $res, OPENSSL_ALGO_MD5);

        openssl_free_key($res);

        return $result;
    }

    /**
     * 速贷 - 私钥加签
     *
     * @param string $param
     * @return string
     */
    public function encryptSignBySdRsaPrivate($param = '')
    {
        //私钥内容
        $private_content = $this->sd_private_key;
        //处理私钥
        $str = chunk_split($private_content, 64, "\n");
        $private_content = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $private_key = openssl_get_privatekey($private_content);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($param, $sign, $private_key, OPENSSL_ALGO_MD5);

        openssl_free_key($private_key);

        $sign = base64_encode($sign);

        return $sign;

    }


    /**
     * 速贷 - 公钥验证签名
     *
     * @param string $param
     * @return string
     */
    public function voidSignBySdRsaPublic($param = '', $encryptRes = '')
    {
        //公钥内容
        $public_content = $this->sd_public_key;
        //处理私钥
        $str = chunk_split($public_content, 64, "\n");
        $public_content = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";

        $res = openssl_get_publickey($public_content);

        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($param, base64_decode($encryptRes), $res, OPENSSL_ALGO_MD5);

        openssl_free_key($res);

        return $result;
    }


    /**
     * 速贷 - 私钥加密
     *
     * @param string $param
     * @return null|string
     */
    public function encryptBySdRsaPrivate($param = '')
    {
        //私钥内容
        $private_content = $this->sd_private_key;
        //处理私钥
        $str = chunk_split($private_content, 64, "\n");
        $private_content = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";

        //判断私钥的可用性
        $private_key = openssl_get_privatekey($private_content);

        $crypto = '';
        $chunks = str_split($param, 117);
        foreach ($chunks as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $private_content);
            $crypto .= $encryptData;
        }

        return $crypto ? base64_encode($crypto) : null;
    }

    /**
     * 速贷 - 公钥解密
     *
     * @param string $param
     * @return string
     */
    public function decryptBySdRsaPublic($param = '')
    {
        $param = base64_decode($param);

        //公钥内容
        $public_content = $this->sd_public_key;
        //处理私钥
        $str = chunk_split($public_content, 64, "\n");
        $public_content = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";

        //判断公钥的可用性
        $public_key = openssl_get_publickey($public_content);

        $crypto = '';
        foreach (str_split($param, 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, $public_content);
            $crypto .= $decryptData;
        }
        //openssl_public_decrypt($encrypted,$decrypted,$this->pu_key);//私钥加密的内容通过公钥可用解密出来
        return $crypto;
    }

    /**
     * 对汇聚支付回调参数进行urldecode解密
     *
     * @param array $params
     * @return array
     */
    public function urldecodeParams($params = [])
    {
        $datas = [];
        $datas['r1_MerchantNo'] = $params['r1_MerchantNo'];
        $datas['r2_OrderNo'] = $params['r2_OrderNo'];
        $datas['r3_Amount'] = $params['r3_Amount'];
        $datas['r4_Cur'] = $params['r4_Cur'];
        //将urlencode字符串进行解码
        //公用回传参数
        $datas['r5_Mp'] = urldecode($params['r5_Mp']);
        $datas['r6_Status'] = $params['r6_Status'];
        $datas['r7_TrxNo'] = $params['r7_TrxNo'];
        $datas['r8_BankOrderNo'] = $params['r8_BankOrderNo'];
        $datas['r9_BankTrxNo'] = $params['r9_BankTrxNo'];
        //支付时间
        $datas['ra_PayTime'] = urldecode(urldecode($params['ra_PayTime']));
        //交易结果通知时间
        $datas['rb_DealTime'] = urldecode(urldecode($params['rb_DealTime']));
        $datas['rc_BankCode'] = $params['rc_BankCode'];
        $datas['hmac'] = $params['hmac'];

        return $datas;
    }
}