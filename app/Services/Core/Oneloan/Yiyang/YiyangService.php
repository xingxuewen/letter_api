<?php

namespace App\Services\Core\Oneloan\Yiyang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Yiyang\YiyangConfig\YiyangConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use App\Models\Orm\UserAgent;
use App\Helpers\UserAgent AS UserAgentUtil;
use App\Helpers\Logger\SLogger;


class YiyangService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {
        //请求url
        $url = YiyangConfig::URL;
        $user_agent = UserAgent::Select(['user_agent'])->where('user_id', '=', $params['user_id'])->orderBy('create_at','Desc')->first();

        $addtime= date('Y-m-d H:i:s',time());
        $arr=[
            'siteid'=>YiyangConfig::SITEID,
            'cscode'=>YiyangConfig::CSCODE,
            'sign'=>md5(YiyangConfig::SECRET.$addtime),
            'realname'=>$params['name'],
            'mobile'=>$params['mobile'],
            'idcard'=>$params['certificate_no'],
            'sex'=> $params['sex'] == 1 ? 'M': 'F',
            'birthday'=>!empty($params['birthday'])?strtotime($params['birthday']):'',
            'add_time'=>$addtime,
            'load_page'=>'',
            'ip'=>isset($params['created_ip']) ?  explode(',',$params['created_ip'])[0] : explode(',',Utils::ipAddress())[0],
            'user_agent' =>isset($user_agent['user_agent']) ? $user_agent['user_agent'] : UserAgentUtil::i()->getUserAgent(),
            'sms_code'=>'',
            'insurance_limit'=>'',
            'payment_type'=>'',
            'delay_seconds'=>'',
            'car'=>'',
            'house'=>'',
            'credit_card'=>'',
            'income'=>'',
            'need_loan'=>'',
            'loan_amount'=>'',
            'province'=>'',
            'city'=>'',
            'children_count'=>'',
            'child_age'=>'',
            'is_married'=>'',
            'education'=>''
        ];

        //请求
        $request = [
            'headers'=>[
                'Content-Type'=>'application/json'
            ],
            'json'=>$arr
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();
    }
}