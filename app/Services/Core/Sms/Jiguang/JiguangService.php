<?php

namespace App\Services\Core\Sms\Jiguang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\Core\Sms\SmsService;
use Log;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Models\Orm\MessageLog;

/**
 * 极光短信通道
 * Class ChangzhuoService
 * @package App\Services\Core\Sms\Changzhuo
 */
class JiguangService extends SmsService
{

    /**
     * 发送短信
     * @param $data
     */
    public function send($data)
    {
        $request = [
            'headers' => [
                'Authorization' =>'Basic '.base64_encode(config('sms.jiguang.appKey').':'.config('sms.jiguang.masterSecret')),
                'Content-Type'=>'application/json'
                ],
            'json' => [
                'mobile' => $data['mobile'],
                'temp_id' =>  config('sms.jiguang.temp_id'),
                'temp_para'=>[
                    'code'=>$data['code']
                ]
            ],

        ];

        $promise = HttpClient::i()->request('POST', config('sms.jiguang.smsSendUrl'), $request);
	    $result = $promise->getBody()->getContents();
	    $this->sendAfter($result, $data);
        return $result;
    }

    /**
     * 发送之后把返回短信商结果入库并执行更新
     */
    public function sendAfter($result, $data = [])
    {
        if ($result)
        {
            MessageLog::where('nid',$data['nid'])->where('mobile', $data['mobile'])->update(['result' => addslashes($result), 'response_time' => date('Y-m-d H:i:s', time())]);
        }
    }

    /**
     * 获取短信模板内容
     */
    public  function getTemple()
    {
        $request = [
            'headers' => [
                'Authorization' =>'Basic '.base64_encode(config('sms.jiguang.appKey').':'.config('sms.jiguang.masterSecret')),
                'Content-Type'=>'application/json'
            ],
            'json' => [
                'temp_id' => config('sms.jiguang.temp_id')
            ],
        ];
        $promise = HttpClient::i()->request('GET', config('sms.jiguang.templatesUrl'),$request);
        $result = $promise->getBody()->getContents();
        return $result;
    }
}
