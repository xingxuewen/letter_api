<?php
namespace App\Services\Core\Platform\Jielebao;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Orm\ShadowLog;
use App\Services\Core\Platform\Jielebao\Config\Config;
use App\Services\Core\Platform\Jielebao\Util\AesUtil;
use App\Models\Factory\OauthFactory;
class JielebaoService{

    /**
     * 联登地址
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchJielebaoUrl($datas = [])
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
        $channel=Config::QUDAO;
        $params = [
            'mobile' => $mobile,
        ];

        $bizData=AesUtil::i()->encode($params);
        $post = array('channel'=>$channel,'bizData'=>$bizData);
        //签名
        $sign = AesUtil::i()->getSign($post);
        //请求数据
        $request = [
            'form_params'=>[
                'channel'=>$channel,
                'bizData'=>$bizData,
                'sign'=>$sign
            ]
        ];
        //请求
        $result = self::execute($request, $url);
        $loginUrl = $page;
        $is_new_user = 0;
        $complete_degree = '';

        if (isset($result)) //成功
        {
            if (isset($result['data'])) //地址
            {
                if(isset($result['data']['skipUrl'])){
                  $loginUrl = $result['data']['skipUrl'];
                }
                if(isset($result['data']['isNewUser'])){
                   if($result['data']['isNewUser']==1){
                       //通过速贷之家推过来的新用户
                       $is_new_user = 3;
                   }elseif($result['data']['isNewUser']==2){
                       //通过速贷之家推的老用户
                       $is_new_user=2;
                   }elseif($result['data']['isNewUser']==3){
                       //其他渠道推过来的用户
                       $is_new_user=4;
                   }
                }
            }
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = Config::QUDAO;
        $datas['apply_url'] = $loginUrl;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
        $datas['is_new_user'] = $is_new_user;
        $datas['complete_degree'] = $complete_degree;
        $datas['qualify_status'] = 99;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $loginUrl;
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
