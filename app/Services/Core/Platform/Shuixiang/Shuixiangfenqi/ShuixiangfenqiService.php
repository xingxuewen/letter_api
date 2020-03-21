<?php
namespace App\Services\Core\Platform\Shuixiang\Shuixiangfenqi;
use App\Helpers\Http\HttpClient;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\Config\ShuixiangfenqiConfig;
use App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\Util\RsaUtil;
use App\Models\Factory\OauthFactory;
/**
 * Created by PhpStorm.
 * User: php
 * Date: 18-10-22
 * Time: 下午5:06
 */
class ShuixiangfenqiService extends PlatformService{

    /**
     *
     *
     * @param array $datas
     * @return array
     */
    public static function fetchShuixiangfenqiUrl($datas = [])
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
        $url = ShuixiangfenqiConfig::URL;

        $params = [
            'userPhone' => $mobile,
            'realName' => $real_name,
            'cardId' => $id_card
        ];
        ksort($params);
        $data=json_encode($params,JSON_UNESCAPED_UNICODE);

        //签名
        $sign = RsaUtil::i()->getSign($data);
        //请求数据
        $request = [
            'headers'=>[
                'Content-Type'=>'application/json',
            ],
            'json'=>[
                'data'=>$data,
                'sign'=>$sign,
            ]
        ];

         //请求
        $result = self::execute($request, $url);
        $loginUrl = $page;
        $is_new_user = 0;
        $complete_degree = '';
        $quality=99;
        $msg='';
        if (isset($result)) //成功
        {
                if (isset($result['data'])) //地址
                {
                    if (isset($result['data']['url'])) //地址
                    {
                        $loginUrl = $result['data']['url'];
                    }
                    if(isset($result['data']['userStatus'])){
                        if($result['data']['userStatus']==0){
                            //通过速贷之家推过来的新用户
                            $is_new_user = 3;
                        }elseif($result['data']['userStatus']==1){
                            //速贷之家老用户
                            $is_new_user=2;
                        }elseif($result['data']['userStatus']==2){
                            //已注册，且其他渠道名称；
                            $is_new_user=4;
                        }
                    }
                    if(isset($result['data']['qualifiStatus'])){
                        $quality=$result['data']['qualifiStatus'];
                    }
                }
                if(isset($result['status'])){
                     if($result['status']==0){
                         $msg='成功';
                     }else{
                         $msg='失败';
                     }
                }

        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = 'SDZJ';
        $datas['apply_url'] = $loginUrl;
        $datas['feedback_message'] = $msg;
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
        $result = utf8_encode($promise->getBody()->getContents());
        $result=json_decode($result,true);
        return $result;
    }

}