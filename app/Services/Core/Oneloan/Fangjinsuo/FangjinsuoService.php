<?php

namespace App\Services\Core\Oneloan\Fangjinsuo;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Fangjinsuo\FangjinsuoConfig\FangjinsuoConfig;

/**
 * 房金所对接
 */
class FangjinsuoService extends AppService
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
        $url = FangjinsuoConfig::REAL_URL;

        //请求
        $request = [
            'form_params' => [
                'telephonenumber' => $params['mobile'],                               // 手机号
                'name' => $params['name'],                                            // 用户名
                'loan_amount' => $params['money'],                                    // 借款金额
                'city' => FangjinsuoConfig::getCity($params['city']),                 // 城市
                'duration' => '12',                                                   // 借款期限
                'accumulation_fund' => $params['accumulation_fund'] == '000' ? 0 : 1, // 是否有公积金
                'social_security' => $params['social_security'] == 1 ? 1 : 0,         // 是否有社保
                'life_insurance' => $params['has_insurance'] == 0 ? 0 : 1,            // 是否有保单
                'particle_loan' => $params['is_micro'] == 1 ? 1 : 0,                  // 是否有微粒贷
                'credit_card' => $params['has_creditcard'] == 1 ? 1 : 0,              // 是否有信用卡
                'car' => $params['car_info'] == '000' ? 0 : 1,                        // 是否有车
                'house' => $params['house_info'] == '000' ? 0 : 1,                    // 是否有房
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

