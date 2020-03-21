<?php

namespace App\Services\Core\Platform\Quhuafenqi;

use App\Helpers\Http\HttpClient;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Quhuafenqi\Config\Config;
use App\Services\Core\Platform\Quhuafenqi\Util\Crypt3Des;
use App\Services\Core\Platform\Quhuafenqi\Util\RsaUtil;

class QuhuafenqiService extends PlatformService
{
    public static function fetchQuhuafenqiUrl($datas = [])
    {
        //原地址
        $page = $datas['page'];

        //在线联登
        $loginUrl = self::fetchLoginService($datas);


        $res['apply_url'] = $loginUrl ? $loginUrl : $page;

        return $res;
    }

    public static function fetchLoginService($datas = [])
    {
        //用户完成度
        //1、2位：模型（默认10）；3、4位（用户属性）：默认01；5位：实名；6位：开卡；7位：借记卡；8位：运营商；9位：芝麻；10位：个人信息；11位：身份证照片；12位：人脸；13位：信用卡
        //以上数字 1表示完成 0表示未完成
        $init_complete_degree = [
            'model' => '10',
            'user_property' => '01',
            'is_real_name' => '0',
            'active_card' => '0',
            'debit_card' => '0',
            'carrieroperator' => '0',
            'sesame' => '0',
            'personal_info' => '0',
            'id_card' => '0',
            'face' => '0',
            'credit' => '0'
            ];
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page'];
        $is_new_user = 0;
        $qualify_status = 99;//未知
        $des_key = RsaUtil::i()->randomkeys(8);//randomkeys(8);
        $key = RsaUtil::i()->public_encrypt($des_key);
        $arr = array(
            'phone' => $mobile
        );
        Crypt3Des::i()->setCrypt3Des($des_key);
        $reqData = Crypt3Des::i()->encrypt(json_encode($arr));

        $sign = RsaUtil::i()->sign($reqData);
        $url = Config::getLoginUrl();
        $param = array(
            "reqData" => $reqData,
            "merchantId" => Config::MERCHANTID,
            "key" => $key,
            "sign" => $sign,
            "oarToken" => '123456',
            "channel" => 'micro_site',
            "product" => 'shoujidai',
            "fromChannel" => 'sudaizj-llcs',
        );
        $request = [
            'json' => $param,
        ];

        $result = self::execute($request, $url);//对接平台返回用户信息进行处理
        $loginUrl = '';
        if ($result['statusCode'] == '00000000')
        {
            $loginUrl = !empty($result['data']['token']) ? 'https://m.shoujidai.com?channel=sudaizj-llcs&qlcode=' . $result['data']['token'] : 'https://m.shoujidai.com?channel=sudaizj-llcs';
        }
        $init_complete_degree['is_real_name'] = isset($result['isRealname']) ? strval(boolval($result['isRealname'])) : '0';
        $complete_degree = implode('', $init_complete_degree);
        $datas['username'] = isset($datas['user']['username']) ? $datas['user']['username'] : '';
        $datas['mobile'] = isset($datas['user']['mobile']) ? $datas['user']['mobile'] : '';
        $datas['channel_no'] = 'sudaizj-llcs';
        $datas['apply_url'] = isset($result['data']['token']) ? $loginUrl : Config::DEFAULT_URL;
        $datas['feedback_message'] = isset($result['msg']) ? $result['msg'] : '';
        $datas['is_new_user'] = $is_new_user;
        $datas['complete_degree'] = $complete_degree;
        $datas['qualify_status'] = $qualify_status;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $loginUrl ? $loginUrl : $page;
    }

    public static function execute($request, $url)
    {
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        return json_decode($result, true);
    }
}