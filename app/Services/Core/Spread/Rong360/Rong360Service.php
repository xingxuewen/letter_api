<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/7/26
 * Time: 19:42
 */

namespace App\Services\Core\Spread\Rong360;

use App\Services\Core\Spread\Rong360\Config\Config;
use App\Services\Core\Spread\Rong360\Util\RsaUtil;
use App\Services\Core\Spread\SpreadService;
use App\Helpers\Http\HttpClient;

class Rong360Service extends SpreadService
{

    /**
     * 融360免密登陆
     * @param $datas
     * @return array
     */
    public static function fetchRong360Url($params = [])
    {
        //配置产品信息
        $config = $params['config'];
        //用户信息
        $user = $params['user'];
        //地址
        $page = $config['url'];

        //实名认证验证
        if(empty($user['real_name']) && empty($user['fake_name']))
        {
             $datas['url'] = $page;
             return $datas;
        }

        //联登请求地址
        $url = Config::DOMAIN_URL . '/api/checkusermobile';

        $datas = [
            'city' => isset($user['city']) ? $user['city'] : '',              // 城市名称 不带市
            'mobile' => $user['mobile'],     // 手机号
            'name' => empty($user['real_name']) ? $user['fake_name'] : $user['real_name'],            // 姓名
            'ts' => time(),                         // 当前时间戳
            'uid' => 'uid_' . $user['sd_user_id'],             // 用户唯一标识
            'utm_medium' => Config::UTM_MEDIUM,         // 固定值
            'utm_source' => Config::UTM_SOURCE,          // 固定值
            'secret_key' => Config::SECRET_KEY,
        ];

        //token值
        $token = RsaUtil::fetchToken($datas);

        unset($datas['secret_key']);
        $datas['token'] = $token;

        //请求参数
        $request = [
            'form_params' => $datas,
        ];

        //dd($request);
        //发起请求
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $resultObj = json_decode($result, true);

        //返回对接地址
        if (isset($resultObj['error']) && $resultObj['error'] == 0) {
            $page = isset($resultObj['redirect_url']) ? $resultObj['redirect_url'] : '';
        }


        $datas['url'] = $page;

        return $datas;
    }
}