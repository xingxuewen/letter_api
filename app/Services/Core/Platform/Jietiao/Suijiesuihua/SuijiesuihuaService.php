<?php

namespace App\Services\Core\Platform\Jietiao\Suijiesuihua;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Models\Orm\ShadowLog;
use App\Services\Core\Platform\Jietiao\Suijiesuihua\Config\Config;
use App\Services\Core\Platform\Jietiao\Suijiesuihua\Util\RsaUtil;
use App\Services\Core\Platform\PlatformService;

/**
 * 借条-随借随花对接
 *
 * Class SuijiesuihuaService
 * @package App\Services\Core\Platform\Jietiao\Suijiesuihua
 */
class SuijiesuihuaService extends PlatformService
{
    /**
     * 联登地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchSuijieUrl($datas = [])
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
        $ip = Utils::ipAddress(); //用户ip
        $real_name = $datas['user']['real_name'];
        $id_card = $datas['user']['idcard'];

        //联登接口
        $url = Config::getLoginUrl();

        $params = [
            'parter_id' => Config::PARTNER_ID,
            'mobile' => $mobile,
            'request_source_ip' => $ip,
            'real_name' => $real_name,
            'id_card' => $id_card
        ];
        //签名
        $sign = RsaUtil::i()->getSign($params);
        //请求数据
        $requestData['ecryption_data'] = $sign;
        $requestData['ecrypt_id'] = Config::ECRYPT_ID;
        $request = [
            'form_params' => $requestData,
        ];

        //请求
        $result = self::execute($request, $url);

        $loginUrl = '';
        if (isset($result)) //成功
        {
            if (isset($result['data']['url'])) //地址
            {
                $loginUrl = $result['data']['url'];
            }

        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = Config::PARTNER_ID;
        $datas['apply_url'] = $loginUrl ? $loginUrl : $page;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
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