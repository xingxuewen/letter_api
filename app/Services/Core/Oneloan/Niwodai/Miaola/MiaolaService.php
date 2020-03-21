<?php

namespace App\Services\Core\Oneloan\Niwodai\Miaola;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Niwodai\Miaola\Config\MiaolaConfig;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * 你我贷-秒啦对接
 */
class MiaolaService extends AppService
{

    /**
     * 借款申请接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed|string   false 未注册　　true 已注册
     */
    public static function apply($params = [], callable $success, callable $fail)
    {
        //链接地址
        $url = MiaolaConfig::REAL_URL;
        $token = MiaolaConfig::getAccessToken();
//        Log::info('token', ['message' => $token, 'code' => 1005]);
        $data = [
            'time' => MiaolaConfig::getMillionTime(),
            'nwd_ext_aid' => MiaolaConfig::ADV_SPACE,
            'phone' => $params['phone'],
            'realName' => $params['realName'],
            'age' => $params['age'],
            'birthTime' => $params['birthTime'],
            'cityName' => $params['cityName'],
            'amount' => $params['amount'],
            'token' => $token,
        ];
        $jsonParams = json_encode($data, JSON_UNESCAPED_UNICODE);
        //整理参数
        $request = [
            'form_params' => [
                'accessCode' => MiaolaConfig::ACCESS_CODE,
                'jsonParam' => $jsonParams,
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

