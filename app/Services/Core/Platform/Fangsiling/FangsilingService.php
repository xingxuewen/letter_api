<?php

namespace App\Services\Core\Platform\Fangsiling;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\Fangsiling\Config\Config;
use App\Services\Core\Platform\Fangsiling\Util\RsaUtil;
use App\Services\Core\Platform\PlatformService;

/**
 * 房司令对接
 *
 * Class FangsilingService
 * @package App\Services\Core\Platform\Fangsiling
 */
class FangsilingService extends PlatformService
{
    /**
     * 房司令联登地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchFangsilingUrl($datas = [])
    {
        //原地址
        $page = $datas['page'];

        //在线联登
        $loginUrl = self::fetchLoginService($datas);

        $res['apply_url'] = $loginUrl ? $loginUrl : $page;

        return $res;
    }

    /**
     * 联登地址
     *
     * @param array $datas
     * @return mixed|string
     */
    public static function fetchLoginService($datas = [])
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page'];

        //联登接口
        $url = Config::URL;

        $timeyamp= time();
        //需要加签的参数
        $params = [
            'sign_type' => 'RSA',
            'biz_data' => json_encode(array('mobile' => $mobile)),
            'version' => '1.0',
            'app_id' => Config::APP_ID,
            'format' => 'json',
            'timestamp' => $timeyamp

        ];

        //签名
        $sign = RsaUtil::i()->getSign($params);

        $request = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'sign' => $sign,
                'sign_type' => 'RSA',
                'biz_data' => json_encode(array('mobile' => $mobile)),
                'app_id' => Config::APP_ID,
                'version' => '1.0',
                'format' => 'json',
                'timestamp' => $timeyamp,
            ],
        ];

        //请求
        $result = self::execute($request, $url);

        $loginUrl = '';
        if (isset($result)) //成功
        {
            if (isset($result['data']['callback_h5_url'])) //地址
            {
                $loginUrl = $result['data']['callback_h5_url'];
            }

        }

        //   对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = Config::APP_ID;
        $datas['apply_url'] = $loginUrl ? $loginUrl : $page;
        $datas['feedback_message'] = isset($result['msg']) ? $result['msg'] : '';
        $datas['is_new_user'] = 99;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $loginUrl ? $loginUrl : $page;
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

}
