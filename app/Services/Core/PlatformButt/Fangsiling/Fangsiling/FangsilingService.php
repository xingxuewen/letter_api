<?php

namespace App\Services\Core\PlatformButt\Fangsiling\Fangsiling;

use App\Helpers\Http\HttpClient;
use App\Services\Core\PlatformButt\PlatformButtService;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\OauthFactory;
use App\Services\Core\PlatformButt\Fangsiling\Fangsiling\Config\Config;
use App\Services\Core\PlatformButt\Fangsiling\Fangsiling\Util\RsaUtil;

class FangsilingService extends PlatformButtService
{


    /**
     * 房司令撞库地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchFangsilingUrl($datas = [])
    {
        //原地址
        $page = $datas['page'];
        //在线撞库
        $resData = self::fetchLoginService($datas);

        $data = self::getAccessLogData($resData, $datas);

        $data['channel_no'] = Config::APP_ID;

        $data['apply_url'] = $page;
        //对接平台返回对接信息记流水
    //    logInfo('', $data);
        $log = OauthFactory::createDataProductEncryptAccessLog($data);
        return $resData;
    }

    /**
     * 联登地址
     *
     * @param array $datas
     * @return mixed|string
     */
    public static function fetchLoginService($datas = [])
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page'];


        //撞库地址
        $url = Config::URL;

        //获取时间戳
        $timeyamp = time();
        //需要加签的参数
        $params = [
            'sign_type' => 'RSA',
            'biz_data' => json_encode(array('user_mobile' => md5($mobile))),
            'version' => '1.0',
            'app_id' => Config::APP_ID,
            'format' => 'json',
            'timestamp' => $timeyamp
        ];


        //签名
        $sign = RsaUtil::i()->getSign($params);

        $request = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'sign' => $sign,
                'sign_type' => 'RSA',
                'biz_data' => json_encode(array('user_mobile' => md5($mobile))),
                'app_id' => Config::APP_ID,
                'version' => '1.0',
                'format' => 'json',
                'timestamp' => $timeyamp,
            ],
        ];

        //请求
        $result = self::execute($request, $url);
        $resData = self::getResultData($result);

        return $resData;

    }

    /**
     * 通用请求
     * @param $request
     * @param $url
     * @return mixed
     */
    public static function execute($request, $url)
    {

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();

        return json_decode($result, true);
    }

    /**
     * 返回数据处理
     * @param array $result
     * @return array
     */
    public static function getResultData($result = [])
    {
//        print_r($result);die;
//        logInfo('数据',$result);
        $resData = [];
        $data = isset($result['data']) ? $result['data'] : [];


        if (isset($data['userStatus'])) {
            if ($data['userStatus'] == 0) {
                // 未注册 => 通过速贷之家推过来的新用户
                $is_new_user = 3;
            } elseif ($data['userStatus'] == 1) {
                // 已注册 且为渠道用户 => 通过速贷之家推的老用户
                $is_new_user = 2;
            } elseif ($data['userStatus'] == 2) {
                // 已注册 且非渠道用户 => 其他渠道推过来的用户
                $is_new_user = 4;
            } else {
                $is_new_user = 99;
            }
        }

        $resData['is_new_user'] = isset($is_new_user) ? $is_new_user : 0;   //平台用户来源
        $resData['qualify_status'] = isset($data['qualifiStatus']) ? $data['qualifiStatus'] : '99'; //用户是否符合资质
        $resData['complete_degree'] = isset($data['complete_degree']) ? $data['complete_degree'] : ''; //用户完成度
        $resData['feedback_message'] = isset($result['msg']) ? $result['msg'] : ''; //反馈信息
        $resData['period_type'] = isset($data['periodType']) ? $data['periodType'] : '0'; //允许的借款期数或天数
        $resData['period'] = isset($data['period']) ? $data['period'] : ''; //允许的借款期数或天数
        $resData['amount_min'] = isset($data['amountMin']) ? $data['amountMin'] : '0'; //此用户在合作机构可借款的最小额度
        $resData['amount_max'] = isset($data['amountMax']) ? $data['amountMax'] : '0'; //此用户在合作机构可借款的最大额度
        $resData['success_rate'] = isset($data['successRate']) ? $data['successRate'] : '0'; //允许的借款期数或天数

        return $resData;
    }

    /**
     * 入库前数据处理
     * @param $resData
     * @param $datas
     * @return mixed
     */
    public static function getAccessLogData($resData, $datas)
    {
        $resData['userId'] = $datas['userId'];
        $resData['username'] = $datas['user']['username'];
        $resData['mobile'] = $datas['user']['mobile'];
        $resData['platformId'] = $datas['platformId'];
        $resData['productId'] = $datas['productId'];
        $resData['product']['product_name'] = $datas['product']['product_name'];
//        logInfo('数据',$resData);
        return $resData;
    }

}