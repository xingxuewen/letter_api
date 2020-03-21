<?php

namespace App\Services\Core\Oneloan\Dazhi;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Dazhi\DazhiConfig\DazhiConfig;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use App\Helpers\Utils;
use App\Helpers\Logger\SLogger;
class DazhiService extends AppService
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
        $url = DazhiConfig::USE_URL;
        
        $data=[
            'Name'=>$params['name'],
            'Birthday'=>!empty($params['birthday'])?date('Y-m-d',strtotime($params['birthday'])):'',
            'Sex'=>DazhiConfig::getSex($params['sex']),
            'Phone'=>$params['mobile'],
            'IP'=>isset($params['created_ip']) ? explode(',',$params['created_ip'])[0] : explode(',',Utils::ipAddress())[0] ,
            'City'=>$params['city'],
            'LoanAmount'=>$params['money']/10000,
            'Houes'=>DazhiConfig::getHouse($params['house_info']),
            'Car'=>DazhiConfig::getCar($params['car_info']),
            'XyCard'=>DazhiConfig::getCredit($params['has_creditcard']),
            'Payway'=>DazhiConfig::getSalaryExtend($params['salary_extend']),
            'Hire'=>DazhiConfig::getOccupation($params['occupation']),
            'Wage'=>DazhiConfig::getSalary($params['salary']),
            'SheBaoTime'=>DazhiConfig::getSocial($params['social_security']),
            'EPFTime'=>DazhiConfig::getFound($params['accumulation_fund']),
            'IDCard'=>$params['certificate_no'],
            'Shouxian'=>DazhiConfig::getInsurance($params['has_insurance']),
            'LiveTime'=>0,
            'SourceId'=>DazhiConfig::SOURCE_ID
        ];

        $v=DazhiConfig::getV($data['Birthday'],$data['Phone']);
        $url=$url.'&v='.$v;
        //请求
        $request = [
            'headers'=>[
                'Content-Type'=>'application/json'
            ],
            'json'=>$data
        ];
//        logInfo('111',$data);
//        logError($url);
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