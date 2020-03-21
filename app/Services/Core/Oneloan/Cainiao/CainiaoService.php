<?php

namespace App\Services\Core\Oneloan\Cainiao;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\DeliveryFactory;
use App\Services\AppService;
use App\Services\Core\Oneloan\Cainiao\Config\CainiaoConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;


class CainiaoService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {

        //请求url
        $url = CainiaoConfig::REAL_URL;
        //获取渠道号
        $channelNid = DeliveryFactory::fetchDeliveryNId($params['user_id']);
        // 获取签名用时间 单位为毫秒
        $milliTime = CainiaoConfig::getMillionTime();
        if($params['money']>=10000 and $params['money']<50000){
            $params['money']=50000;
        }
        //请求
        $request = [
            'form_params' => [
                'time' => $milliTime,//时间
                'telephone' => $params['mobile'],                                       // 手机号
                'applyName' => $params['name'],                                         // 用户名
                'applyIdCard' => $params['certificate_no'],                             // 身份证号
                'cityName' => CainiaoConfig::getCity($params['city']),                  // 城市
                'familyCity'=> CainiaoConfig::getCity($params['city']),
                'education' => 1,                                                       // 教育程度：1、本科及以上
                'loanAmount' => ceil($params['money'] / 10000),                   // 借款金额:向上取整
                'loanPeriod' => '12',                                                   // 借款期限
                'loanUse' => '消费贷款',                                                 // 贷款目的
                'workType' => CainiaoConfig::getOccupation($params['occupation']),      // 职业
                'creditRecord' => 1,                                                    // 信用记录：1、信用良好无逾期
                'haveWeiLi' => ($params['is_micro'] == 1) ? 8000 : $params['is_micro'], // 微粒贷额度
                'companyName' => '保密',                                                // 公司名称
                'workingYears' => isset($params['work_hours'])? CainiaoConfig::getWorkTime($params['work_hours']) : '',        //工作年限
                'income' => CainiaoConfig::getSalary($params['salary']),               //收入情况
                'wagesType' => isset($params['salary_extend']) ? ($params['salary_extend'] == '001' ? 0 : 2) : 0,             //收入形式
                'socialType' => $params['social_security'] == 1 ? 3 : 0,                                                       // 是否有社保
                'fundType' => $params['accumulation_fund'] == '000' ? 0 : 3,                                                   // 是否有公积金
                'businessLicense' => isset($params['business_licence']) ? 1 : '',                                              // 是否有营业执照
                'annualflow' => 0,                                                                                             // 年流水：10万
                'plantingDuration' => isset($params['business_licence']) ? ($params['business_licence'] == '001' ? 0 : 4) : '',// 经营年限
                'estate' => $params['house_info'] == '000' ? 0 : 1,//名下房产:0、无房产 1、有房产可做抵押
                'estateType' => $params['house_info'] == '001' ? 6 : '',//房产类型：有房：其他
                'estateValuation' => $params['house_info'] == '001' ? '100万' : '',//房产估值
                'carType'=>$params['car_info'] == '000' ? 0 : 2,
                'carAssetsValuation'=>$params['car_info']=='001'?'20万':'',
                'personalInsurance' => $params['has_insurance'] == 0 ? 0 : 1,                                                  // 是否有保单
                'insuredCompany' => $params['has_insurance'] == 1 ? '其他': '',                                                // 保险种类
                'insuranceValuation' => $params['has_insurance'] == 1 ? '30万': '',                                            // 保险价值

                'creditCardLimit' => $params['has_creditcard'] == 1 ? '3万以上' : 0,                                           // 信用卡额度
                'sex' => $params['sex'] == 1 ? 0 : 1,                                                                          // 性别
                'applyIp' => isset($params['created_ip']) ? $params['created_ip'] : Utils::ipAddress(),
                'age'=> Utils::getAge($params['birthday']),
//               'channel_nid' => 'yijiandai_'.$channelNid,                                                                     // 用户渠道标识
            ],
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();
    }
}