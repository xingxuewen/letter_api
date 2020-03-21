<?php

namespace App\Services\Core\Oneloan\Hengchang;

use App\Helpers\Http\SoapClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Hengchang\HengchangConfig\HengchangConfig;
use App\Strategies\SpreadStrategy;

/**
 * 恒昌对接
 */
class HengchangService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [])
    {
        //请求url
        $url = HengchangConfig::URL . '/Api/WebService/PushData.php?wsdl';

        //cred:一个 json 字符串， 用于传入用户名密码
        $credential = [
            'UserName' => HengchangConfig::USERNAME,
            'Password' => HengchangConfig::PASSWORD,
        ];

        //加密
        $encryptCred = HengchangConfig::encrypt(json_encode($credential, JSON_UNESCAPED_UNICODE));

        //request: 一个 json 字符串，用于传入待推送的用户数据
        $req = [
            'Id' => HengchangConfig::HENGCHANG_CODE.'_'.time().'_'.$params['id'],
            'Name' => $params['name'],
            'TelNo' => $params['mobile'],
            'Age' => $params['age'],  //选填
            'IdNumber' => isset($params['certificate_no']) ? $params['certificate_no'] : '-',
            'Salary' => SpreadStrategy::formatSalaryAverage($params), //选填 月均收入，单位：元
            'Loan' => intval($params['money']), //选填 贷款额度，单位：元
//            'Credit' => $params['credit'], //选填 征信记录
            'City' => $params['city'], //必填 城市(需精确到区县，格式如下：xx省|xx市|xx区或xx县)
            'CityCode' => $params['cityCode'], //必填,六位数字
            'Career' => HengchangConfig::formatOccupation($params), //选填 职业
//            'CompanyType' => $params['companyType'], //选填 企业类型
//            'CreditCondition' => $params['creditCondition'], //选填 信用情况
//            'ServiceAge' => HengchangConfig::formatWorkHours($params), //选填 当前单位工龄，单位：年
//            'LoanPeriod' => $params['loanPeriod'], //选填 借款期限（月）
            'House' => HengchangConfig::formatHouseInfo($params), //选填 是否名下有房产
            'Car' => $params['car_info'] == '000' ? '否' : '是', //选填 是否名下有车
            'Gongjijin' => $params['accumulation_fund'] == '000' ? '否' : '是', //选填 是否有本地公积金
            'SocialSecurity' => $params['social_security'] == 0 ? '否' : '是', //选填 是否有社保
            'Policy' => $params['has_insurance'] == 0 ? '否' : '是', //选填 是否有保单
            'CreditCard' => $params['has_creditcard'] == 0 ? '否' : '是', //选填 是否有信用卡
        ];

        //加密
        $encryptReq = HengchangConfig::encrypt(json_encode($req, JSON_UNESCAPED_UNICODE));
        //code
        $code = HengchangConfig::HENGCHANG_CODE;

//        $client = new \SoapClient($url);
//        $ret = $client->pushUserData($code, $encryptReq, $encryptCred);

        $ret = SoapClient::i($url)->pushUserData($code, $encryptReq, $encryptCred);

        $res = json_decode($ret, true);

        return $res;
    }
}

