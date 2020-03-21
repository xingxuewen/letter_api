<?php


namespace App\Services\Core\Platform\Danhuahua\Danhuahua;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\Danhuahua\Danhuahua\Config\Config;
use App\Services\Core\Platform\Danhuahua\Danhuahua\Util\RsaUtil;
use App\Services\Core\Platform\PlatformService;

/**蛋花花
 * Class DanhuahuaService
 * @package App\Services\Core\Platform\Danhuahua\Danhuahua
 */
class DanhuahuaService extends PlatformService
{
    /**
     * 蛋花花联登地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchDanhuahuaUrl($datas = [])
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

        //联登接口
        $url = Config::getLoginUrl();

        $params = [
            'ua' => Config::UA,
            'key' => Config::KEY,
        ];

        //签名
        $signKey = RsaUtil::i()->getPublicSign($params);
        //获取时间戳
        $timeyamp = time();
        $ginseng = [
            'phone' => $mobile,
            'source' => Config::SOURCE
        ];
        $args = json_encode($ginseng);
        $sign = md5($signKey . $timeyamp . $signKey . $args . $signKey);

        //请求数据
        $vargs = http_build_query([
            'ua' => Config::UA,
            'args' => $args,
            'timestamp' => $timeyamp,
            'loginType' => "0",
            'sign' => $sign,
        ]);

        $url = $url . '?' . $vargs;
        //请求
        $result = self::execute($url);

        logInfo('请求',$result);
        $loginUrl = $page;
        $is_new_user = 0;
        $complete_degree = '';
        $quality = 99;
        if (isset($result)) //成功
        {
            if (isset($result['data'])) //地址
            {
                if (isset($result['data']['url'])) //地址
                {
                    $loginUrl = $result['data']['url'];
                }
                if (isset($result['data']['status'])) {
                    if ($result['data']['status'] == 0) {
                        //通过速贷之家推过来的新用户
                        $is_new_user = 3;
                    } elseif ($result['data']['status'] == 1) {
                        //速贷之家老用户
                        $is_new_user = 2;
                    } elseif ($result['data']['status'] == 2) {
                        //已注册，且其他渠道名称；
                        $is_new_user = 4;
                    }
                }
                if (isset($result['data']['qualifiStatus'])) {
                    $quality = $result['data']['qualifiStatus'];
                }
            }

        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = Config::UA;
        $datas['apply_url'] = $loginUrl;
        $datas['feedback_message'] = isset($result['msg']) ? $result['msg'] : '';
        $datas['is_new_user'] = $is_new_user;
        $datas['complete_degree'] = $complete_degree;
        $datas['qualify_status'] = $quality;

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
    public static function execute($url)
    {

        $promise = HttpClient::i(['verify' => false])->request('GET', $url);
        $result = $promise->getBody()->getContents();

        return json_decode($result, true);
    }

}