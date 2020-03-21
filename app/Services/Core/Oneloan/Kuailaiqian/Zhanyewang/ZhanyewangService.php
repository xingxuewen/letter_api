<?php

namespace App\Services\Core\Oneloan\Kuailaiqian\Zhanyewang;

use App\Helpers\DateUtils;
use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Services\AppService;
use  App\Services\Core\Oneloan\Kuailaiqian\Zhanyewang\Config\Config;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use App\Models\Factory\UserSpreadFactory;
use App\Constants\SpreadNidConstant;

/**
 * 展业王
 *
 * Class JibaodaiService
 * @package App\Services\Core\Platform\Jibaodai\Jibaodai
 */
class ZhanyewangService extends AppService
{

    public static function spread($params = [], callable $success, callable $fail)
    {
        //地址
        $url = Config::URL;
        $params['city']= !empty($params['city']) ? $params['city'] : '北京市';
        //参数处理
        $request = [
            'form_params' => [
                'code' => Config::CHANNEL_NO,
                'tradeno'=>$params['order_num'],
                'truename' =>mb_substr($params['name'],0,1,'utf-8').'**',  //必填 名字
                'mobile' => md5($params['mobile']),  //必填 手机号
                'city'=>$params['city'],
                'province'=>!empty(UserSpreadFactory::getProvince($params['city']))?UserSpreadFactory::getProvince($params['city']):'北京',
                'loanamount'=>Config::getMoney($params['money']),
                'birthday'=>!empty($params['birthday'])?date('Ymd',strtotime($params['birthday'])):'',
                'gender'=>$params['sex'] == 1 ? 'M' : 'F',   // 1男M,0女F 必传
                'fangchan'=>Config::getHouse($params['house_info']),
                'car'=>Config::getCar($params['car_info']),
                'zhiye'=>Config::getOccupation($params['occupation']),
                'gongzi'=>Config::getSalaryExtend($params['salary_extend']),
                'shouru'=>Config::getSalary($params['salary']),
                'shebao'=>Config::getSocSec($params['social_security']),
                'gongling'=>Config::getWorktime($params['work_hours']),
                'havedan'=>Config::getInsurance($params['has_insurance']),
                'havewld'=>Config::getIsmicro($params['is_micro']),
                'zhizhao'=>Config::getZhizhao($params['business_licence']),
            ],
        ];
        logInfo('request', ['r' => $request, 'url' => $url]);
        $promise = HttpClient::i()->requestAsync('POST', $url, $request);
        $promise->then(
            function (ResponseInterface $res) use ($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use ($fail) {
                $fail($e);
            }
        );

        $promise->wait();

    }

   //收到回调以后推送
    public static function pushdata($params = [], callable $success, callable $fail){
        //地址
        $url = Config::PUSH_URL;
        $request=[
            'form_params'=>[
                'code' => Config::CHANNEL_NO,
                'TradeNo'=>$params['order_num'],
                'Mobile'=>$params['mobile'],
                'Truename'=>$params['name']
            ]
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);
        $promise->then(
            function (ResponseInterface $res) use ($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use ($fail) {
                $fail($e);
            }
        );

        $promise->wait();
    }
}