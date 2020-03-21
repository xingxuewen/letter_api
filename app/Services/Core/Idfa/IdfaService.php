<?php

namespace App\Services\Core\Idfa;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Services\AppService;

/**
 * Idfa激活回调接口
 *
 * Class IdfaService
 * @package App\Services\Core\Idfa
 */
class IdfaService extends AppService
{
    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * IDFA激活回调对接
     *
     * @param $datas
     * @return mixed
     */
    public function toIdfaService($datas)
    {
        //请求地址
        $url = self::API_IDFA_TICK_URL . '/v1/tick/back';

        //post 传值数据
        $request = [
            'form_params' => [
                'source' => empty($datas['source']) ? 'default' : $datas['source'],
                'appid' => $datas['appName'],
                'idfa' => $datas['idfaId'],
                'user_agent' => UserAgent::i()->getUserAgent(),
            ],
        ];

        //发送请求
        $promise = HttpClient::i()->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        //日志记录
        logInfo('idfa_tick_result_' . $datas['idfaId'], ['data' => $result]);

        //请求api成功
        if ($result && $result['code'] == 200 && $result['error_code'] == 0) {

            return true;
        }

        return false;
    }

}