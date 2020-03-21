<?php

namespace App\Services\Core\Oneloan\Youli;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Youli\YouliConfig\YouliConfig;

/**
 * 有利对接
 */
class YouliService extends AppService
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
        $url = YouliConfig::URL .'?aid=' . YouliConfig::CHANNEL_ID;
        //参数都是必须的
        $request = [
            'form_params' => [
                'name' => $params['name'],
                'mobile' => $params['mobile'],
                'idCard' => $params['idCard'],
                'loanAmount' => $params['loanAmount'],
                'income' => $params['income'],
                'ishouse' => $params['ishouse'],
                'iscar' => $params['iscar'],
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

