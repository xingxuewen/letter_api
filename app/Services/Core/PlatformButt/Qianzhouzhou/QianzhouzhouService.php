<?php

namespace App\Services\Core\PlatformButt\Qianzhouzhou;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\DataUserAccessFactory;
use App\Models\Factory\OauthFactory;
use App\Services\Core\PlatformButt\PlatformButtService;
use App\Services\Core\PlatformButt\Qianzhouzhou\Config\Config;
use App\Services\Core\PlatformButt\Qianzhouzhou\Util\RsaUtil;

/**
 * 钱周周对接
 * Class QianzhouzhouService
 * @package App\Services\Core\Platform\Qianzhouzhou
 */
class QianzhouzhouService extends PlatformButtService
{
    /**
     * 钱周周对接地址
     *
     * @param array $datas
     * @return array|bool
     */
    public static function fetchQianzhouzhouUrl($datas = [])
    {
        $page = $datas['page'];
        //撞库数据处理
        $noRepeatService = self::fetchNoRepeatService($datas);
        //查询数据处理
        $selectService = self::fetchSelectService($datas);

        $noRepeatService['apply_url'] = $page;

        return $noRepeatService ? $noRepeatService : [];
    }

    /**
     * 查询用户贷款进度
     * @param array $datas
     * @return bool
     */
    public static function fetchSelectService($datas = [])
    {
        $mobile = $datas['user']['mobile']; //手机号

        //查询地址
        $selectUrl = Config::getSelectUrl();
        $selectParams = [
            'parterId' => Config::PARTNER_ID,
            'userPhone' => $mobile,
        ];
        //签名
        $sign = RsaUtil::i()->getSign($selectParams);
        $params['sign_val'] = $sign;
        $selectRequest = [
            'form_params' => $params,
        ];
        //请求
        $result = self::execute($selectRequest, $selectUrl);
        //请求结果数据处理
        $resultData = self::getSelectResultData($result);
        //需要产品数据
        $needData = self::getNeedProductInfo($datas);
        $data = array_merge($resultData, $needData);
        //插入流水
        $log = DataUserAccessFactory::insertDataUserAccessLog($data);
        //插入主表
        $progress = DataUserAccessFactory::updateDataUserAccess($data);
        //响应表
        $data['access_id'] = $progress;
        $text = DataUserAccessFactory::updateDataUserAccessText($data);

        if ($log && $progress && $text) //判断
        {
            return true;
        }

        return false;
    }

    /**
     * 撞库service
     * @param $datas
     * @return bool
     */
    public static function fetchNoRepeatService($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page'];
        $ip = Utils::ipAddress(); //用户ip

        // 排重(撞库)接口地址
        $noRepeatUrl = Config::getNorepeatUrl();

        $params = [
            'parterId' => Config::PARTNER_ID,
            'userPhone' => $mobile,
            'requestSourceIp' => $ip,
            'cardId' => '',
            'realName' => '',
        ];
        //签名
        $sign = RsaUtil::i()->getSign($params);
        $params['sign_val'] = $sign;
        $request = [
            'form_params' => $params,
        ];

        //请求
        $result = self::execute($request, $noRepeatUrl);
        //请求结果数据处理
        $resData = self::getResultData($result);

        //对接平台返回用户信息进行处理
        $data = self::getAccessLogData($resData, $datas);
        $data['channel_no'] = Config::CHANNEL_NO;
        $data['apply_url'] = $page;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($data);

        return $data;
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
        $resData = [];
        $data = isset($result['data']) ? $result['data'] : [];
        if (isset($data['userStatus'])) {
            if ($data['userStatus'] == 0) {
                // 未注册 => 通过速贷之家推过来的新用户
                $is_new_user = 0;
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
        $resData['feedback_message'] = isset($result['message']) ? $result['message'] : ''; //反馈信息
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

        return $resData;
    }

    /**
     * 返回结果数据处理
     * @param array $result
     * @return array
     */
    public static function getSelectResultData($result = [])
    {
        $resData = [];
        $data = isset($result['data']) ? $result['data'] : [];
        if (isset($data['userStatus'])) {
            if ($data['userStatus'] == 0) {
                // 未注册 => 通过速贷之家推过来的新用户
                $is_new_user = 0;
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
        $resData['order_status'] = isset($data['orderStatus']) ? $data['orderStatus'] : '99'; //此用户是否有在途订单
        $resData['blacklist_status'] = isset($data['blacklistStatus']) ? $data['blacklistStatus'] : '99'; //此用户是否黑名单用户
        $resData['rejected_status'] = isset($data['rejectedStatus']) ? $data['rejectedStatus'] : '99'; //此用户30天内是否被拒过
        $resData['overdue_status'] = isset($data['overdueStatus']) ? $data['overdueStatus'] : '99'; //此用户是否有逾期
        $resData['overdue_days'] = isset($data['overdueDays']) ? $data['overdueDays'] : ''; //逾期天数
        $resData['response_text'] = json_encode($result);    //逾期天数

        return $resData;
    }

    /**
     * 产品信息
     * @param array $datas
     * @return array
     */
    public static function getNeedProductInfo($datas = [])
    {
        $data = [];
        $data = [
            'mobile' => $datas['user']['mobile'],
            'user_id' => $datas['user']['user_id'],
            'product_id' => $datas['product']['product_id'],
            'product_name' => $datas['product']['product_name'],
            'created_ip' => $datas['user']['created_ip'],
            'status' => 1,
        ];

        return $data ? $data : [];
    }
}
