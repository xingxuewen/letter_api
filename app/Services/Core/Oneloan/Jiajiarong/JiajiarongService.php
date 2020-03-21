<?php

namespace App\Services\Core\Oneloan\Jiajiarong;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\DeliveryFactory;
use App\Services\AppService;
use App\Services\Core\Oneloan\Jiajiarong\Config\JiajiarongConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use App\Helpers\Logger\SLogger;
use App\Models\Orm\UserAgent as UserAgent;
class JiajiarongService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @param $success
     * @param $fail
     * @return mixed
     */
    public static function register($datas = [], callable $success, callable $fail)
    {
        $v=md5($datas['mobile'].JiajiarongConfig::CID.JiajiarongConfig::KEY);
        $user_agent = UserAgent::Select(['user_agent','create_ip'])->where('user_id', '=', $datas['user_id'])->orderBy('create_at','Desc')->first();
        $request = [
            'form_params' => [
                'name' =>  $datas['name'],
                'age' =>  $datas['age'],
                'birthday' =>  date("Y-m-d", strtotime($datas['birthday'])),
                'sex'=>$datas['sex'] == 1 ? 1 : 2,
                'mobile'=>$datas['mobile'],
                'ip'=>isset($user_agent['create_ip']) ?  explode(',',$user_agent['create_ip'])[0] : explode(',',Utils::ipAddress())[0],
                'city'=>JiajiarongConfig::getCity($datas['city']),
                'loan_amount'=>ceil($datas['money']/10000),//单位:万
                'houses'=>JiajiarongConfig::getHouse($datas['house_info']),
                'car'=>JiajiarongConfig::getCar($datas['car_info']),
                'life_policy'=>$datas['has_insurance'] == 0 ? 2 : 1,
                'epf_time'=>JiajiarongConfig::getAccFund($datas['accumulation_fund']),
                'social_security'=>$datas['social_security']==0?1:3,
                'particle_loan'=>'无',
                'sesame_credit'=>'无',
                'credit_card'=>'无',
                'work_time'=>'无',
                'income_range'=>'无',
                'reg_time'=>'无',
                'tax'=>'无'
            ],
        ];
//        logInfo('jiajiarong',$request);
        // 获取接口
        $url = JiajiarongConfig::URL.'?cid='.JiajiarongConfig::CID.'&v='.$v.'&src='.JiajiarongConfig::SRC;

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