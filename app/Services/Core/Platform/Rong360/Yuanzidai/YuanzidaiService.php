<?php
namespace App\Services\Core\Platform\Rong360\Yuanzidai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Rong360\Yuanzidai\Config\YuanzidaiConfig;
use App\Services\Core\Platform\Rong360\Yuanzidai\Util\AesUtil;


class YuanzidaiService extends PlatformService{

    /**
     *
     *
     * @param array $datas
     * @return array
     */
    public static function fetchYuanzidaiUrl($datas = [])
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
        $real_name = $datas['user']['real_name'];
        $id_card = $datas['user']['idcard'];

        //联登接口
        $url = YuanzidaiConfig::URL;

        $data= [
                'mobile' => $mobile,
                'idcard' => $id_card,
                'name' => $real_name,
         ];

        $biz_data= AesUtil::i()->ascEncode($data);
        $sign =  AesUtil::i()->getSign(md5($biz_data));

        //请求数据
        $request = [
            'headers'=>[
                'Content-Type'=>'application/json'
            ],
            'json'=>[
                'biz_data'=>$biz_data,
                'sign'=>$sign
            ]
        ];


        //请求
        $result = self::execute($request, $url);

        $loginUrl = $page;
        $is_new_user = 0;
        $complete_degree = '';
        $quality=99;
        if (isset($result)) //成功
        {
            if (isset($result['data'])) //地址
            {
                if(isset($result['data']['redirectUrl'])) {
                    $loginUrl = $result['data']['redirectUrl'];
                }
                if(isset($result['data']['old'])){
                    if($result['data']['old']==true){
                        $is_new_user=2;
                    }elseif($result['data']['old']==false){
                        $is_new_user=3;
                    }

                }
            }

        }
        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = 'SDZJ';
        $datas['apply_url'] = $loginUrl;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
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
    public static function execute($request, $url)
    {
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = AesUtil::i()->ascDecode($result);//解密数据
        $result = json_decode($result, true);
        return $result;
    }


}