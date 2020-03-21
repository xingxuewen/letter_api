<?php

namespace App\Services\Core\Oneloan\Zhijiechedai;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Models\Cache\CommonCache;
use Carbon\Carbon;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Zhijiechedai\ZhijiechedaiConfig\ZhijiechedaiConfig;

/**
 * 智借车贷对接
 * Class ZhijiechedaiService
 * @package App\Services\Core\Platform\Zhongtengxin
 */
class ZhijiechedaiService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function spread($params = [], callable $success, callable $fail)
    {
        //获取token
        $token = self::getToken();
        //请求url
        $url = ZhijiechedaiConfig::REAL_URL.'api/v1/channel/flow?access_token='.$token;
        //请求
        $request = [
            'json' => [
                'name' => $params['name'],                            // 用户名
                'mobile' => $params['mobile'],                        // 手机号
                'city' => $params['city'],                            // 城市
                'idNo' => $params['certificate_no'],                  // 身份证号
                'age' => $params['age'],                              // 年龄
                'sex' => $params['sex'] == 1 ? 1 : 2,                 // 性别
                'marriage' => 0,                                      // 婚姻状况
                'eduInfo' => '',                                      // 教育状况
                'companyAttribute' => '未知',                          // 单位性质
                'workExperience' => '',                               // 工作年限
                'incomeInfo' => ZhijiechedaiConfig::formatSalary($params),                   // 月收入
                'salaryGetForm' => $params['salary_extend'] == '001' ? true : false,         // 是否打卡发薪
                'creditCard' => $params['has_creditcard'] == 1 ? true : false,               // 是否有信用卡
                'houseProperty' => $params['house_info'] == '000' ? false : true,            // 是否有房产
                'carProperty' => $params['car_info'] == '000' ? false : true,                // 是否有车
                'insurance' => $params['has_insurance'] == 0 ? false : true,                 // 是否有寿险
                'socialSecurity' => $params['social_security'] == 1 ? true : false,          // 是否有社保
                'accumulationFund' => $params['accumulation_fund'] == '000' ? false : true,  // 是否有公积金
                'loan' => $params['money'],                           // 借款金额
                'channel' => ZhijiechedaiConfig::CHANNEL_CODE,        // 渠道号
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

    /*
     * 获取token
     * return string $token
     */
    public static function getToken(){
        $token = CommonCache::getCache(CommonCache::ZHIJIECHEDAI_TOKEN);
        if (empty($token)) {
            //请求url
            $url = ZhijiechedaiConfig::REAL_URL.'/channel/'.ZhijiechedaiConfig::CLIENT_ID.'/token';
            $response = HttpClient::i()->request('GET', $url);
            $result = $response->getBody()->getContents();
            $result = json_decode($result, true);
            if ($result['code'] == '200') {
                $token = $result['result'];
                CommonCache::setCache(CommonCache::ZHIJIECHEDAI_TOKEN, $token, Carbon::now()->addMinutes(100));
            }
        }
        return $token;
    }

}