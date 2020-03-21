<?php

namespace App\Services\Core\Platform\Kami\Kami;

use App\Constants\OauthConstant;
use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\UserVipFactory;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\Kami\Kami\Config\Config;
use App\Services\Core\Platform\Kami\Kami\Util\RsaUtil;
use App\Services\Core\Platform\PlatformService;

/**
 * 卡密-卡密对接
 * Class KamiService
 * @package App\Services\Core\Platform\Kami\Kami
 */
class KamiService extends PlatformService
{
    /**
     * 联登地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchKamiUrl($datas = [])
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
        $type = isset($datas['type']) ? $datas['type'] : OauthConstant::KAMI_CASH_LOGIN_TYPE;   // 1 办卡联登    2 H5取现联登
        $ip = Utils::ipAddress(); //用户ip

        //判断是否是会员
        $is_vip = UserVipFactory::checkIsVip($datas);

        //联登接口
        $url = Config::getLoginUrl();

        $params = [
            'mobile' => $mobile,
            'orderid' => Config::ORDER_ID,
            'channel' => Config::CHANNEL_ID,
            'type' => $type,
            'member' => $is_vip ? 1 : 0,
        ];

        //签名
        $sign = RsaUtil::i()->getSign($params);

        //请求数据
        $params['Sign'] = $sign;
        $request = [
            'json' => $params,
        ];

        //请求
        $result = self::execute($request, $url);

        $loginUrl = '';
        if (isset($result) && $result['code'] = '10000') //成功
        {
            if (isset($result['result']['msg']['jumpurl'])) //地址
            {
                $loginUrl = $result['result']['msg']['jumpurl'];
            }

        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = Config::CHANNEL_ID;
        $datas['apply_url'] = $loginUrl ? $loginUrl : $page;
        $datas['feedback_message'] = isset($result['result']['msg']['message']) ? $result['result']['msg']['message'] : '';
        $datas['is_new_user'] = 99;

        //对接平台返回对接信息记流水
        if (isset($datas['productId']) && !empty($datas['productId'])) //产品点击立即申请对接统计流水
        {
            $log = OauthFactory::createDataProductAccessLog($datas);
        }
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