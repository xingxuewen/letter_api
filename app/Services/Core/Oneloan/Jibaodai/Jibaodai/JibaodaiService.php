<?php

namespace App\Services\Core\Platform\Jibaodai\Jibaodai;

use App\Helpers\DateUtils;
use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Jibaodai\Jibaodai\Config\Config;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Jibaodai\Jibaodai\Util\RsaUtil;
use Psr\Http\Message\ResponseInterface;

/**
 * 吉宝贷
 *
 * Class JibaodaiService
 * @package App\Services\Core\Platform\Jibaodai\Jibaodai
 */
class JibaodaiService extends AppService
{

    public static function spread($params = [], callable $success, callable $fail)
    {
        //地址
        $url = Config::URL;
        $params['applyTime'] = date('Y-m-d H:i:s', time());

        //验签
        $sign = RsaUtil::generateSign($params);

        //参数处理
        $request = [
            'json' => [
                'channelNo' => Config::CHANNEL_NO,
                'name' => $params['name'],  //必填 名字
                'mobile' => $params['mobile'],  //必填 手机号
                'ip' => isset($params['created_ip']) ? $params['created_ip'] : Utils::ipAddress(),  //必填 ip
                'receivedChannel' => 'PC',  //获客渠道:PC or WAP. 必传
                'idCard' => $params['certificate_no'] ?: '', //18位身份证号，可以为空
                'sex' => $params['sex'] == 1 ? 'M' : 'F',   // 1男M,0女F 必传
                'birth' => RsaUtil::formatBirthday($params['birthday']), //出生日期，（1983-05-01）必传
                'city' => $params['city'] ? RsaUtil::formatCity($params['city']) : '北京', //去掉市
                'income' => '',
                'loanAmount' => $params['money'],
                'entryDate' => '',
                'age' => $params['age'],
                'hasCreditCard' => $params['has_creditcard'] == 1 ? 'yes' : 'no', //yes 表示有，no 表示没有 ，必传
                'hasHouse' => $params['house_info'] == '000' ? 'no' : 'yes', //yes有房，no没有 必传
                'hasHousingLoan' => $params['house_info'] = '001' ? 'yes' : 'no', //yes有房贷，no没有 必传
                'hasCar' => $params['car_info'] == '000' ? 'no' : 'yes', //yes有车，no没车 必传
                'hasCarLoan' => $params['car_info'] == '001' ? 'yes' : 'no', //yes有车贷，no没车 必传
                'hasLifeInsurance' => $params['has_insurance'] == '0' ? 'no' : 'yes', //yes有，no没有 必传
                'insurancePay' => '',
                'welfare' => $params['social_security'] == 0 ? 0 : 2, //0无社保，1一年以内， 2一年以上 必传
                'houseFund' => RsaUtil::formatAccumulationFund($params['accumulation_fund']),
                'userAgent' => '',
                'applyTime' => $params['applyTime'], //申请时间，如：“2017-03-01 18:03:21” 必传
                'sign' => $sign,
            ],
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use ($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use ($fail) {
                $fail($e);
            }
        );

        $promise->wait();

    }

}