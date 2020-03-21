<?php

namespace App\Services\Core\Oneloan\Youli;

use App\Helpers\DateUtils;
use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Youli\YouliConfig\YoulinewConfig;
use App\Helpers\Logger\SLogger;
/**
 * 有利2对接
 */
class YoulinewService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {
        //请求url
        $url = YoulinewConfig::URL;
        //参数都是必须的
        $request = [
            'form_params' => [
                'aid'=>$params['aid'],
                'name' => $params['name'],
                'phone' => $params['phone'],
                'idcard' => $params['idCard'],
                'birth'=>$params['birth'],
                'gender'=>$params['gender'],
                'province'=>$params['province'],
                'city'=>$params['city'],
                'loan' =>$params['loan'],
                'income' => $params['income'],
                'ishouse' => $params['ishouse'],
                'iscar' => $params['iscar'],
                'ins'=>$params['ins'],
                'hf'=>$params['hf'],
                'si'=>$params['si'],
                'ip'=> $params['ip'],
                'ua'=> $params['ua'],
                'cartype'=>''
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

