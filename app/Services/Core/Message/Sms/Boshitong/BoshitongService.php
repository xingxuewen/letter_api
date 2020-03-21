<?php

namespace App\Services\Core\Message\Sms\Boshitong;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Models\Orm\MessageLog;
use App\Services\Core\Sms\SmsService;

/**
 * 博士通短信
 *
 * Class BoshitongService
 * @package App\Services\Core\Sms\Boshitong
 */
class BoshitongService extends SmsService
{

    /**
     * 发送短信
     *
     * @param $data
     * @param array $config
     * @return string
     */
    public function send($data, $config = [])
    {
        //密码md5加密小写
        $pwd = strtolower(md5($config['password']));
        $request = [
            'form_params' => [
                'uid' => $config['username'],
                'pwd' => $pwd,
                'mobile' => $data['mobile'],  //发送多个手机号用','隔开的字符串
                'msg' => $config['sign'] . $data['message'],
                'srcphone' => $config['id_code'],   //发送长号
            ],
        ];

        $response = HttpClient::i()->request('POST', $config['url'], $request);
        $result = $response->getBody()->getContents();
        $this->sendAfter($result, $data);

        return $result;
    }

    /**
     * 发送之后把返回短信商结果入库并执行更新
     */
    public function sendAfter($result, $data = [])
    {
        if ($result) {
            MessageLog::where('nid', $data['nid'])->where('mobile', $data['mobile'])->update(['result' => addslashes($result), 'response_time' => date('Y-m-d H:i:s', time())]);
        }
    }
}