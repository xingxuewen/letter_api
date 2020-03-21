<?php

namespace App\Services\Core\Sms\Chuanglan;

use App\Helpers\Logger\SLogger;
use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use Log;

/**
 * 创蓝短信通道
 * Class ChuanglanService
 * @package App\Services\Core\Sms\Chuanglan
 */
class ChuanglanService extends SmsService
{

    /**
     *
     * @param $data
     */
    public function send($data)
    {
        //获取主账户、子账户信息
        $data = $this->fetchAccount($data);

        $request = [
            'form_params' => [
//                'account' => config('sms.chuanglan.smsAccount'),
//                'pswd' => config('sms.chuanglan.smsPassword'),
                'account' => $data['account'],
                'pswd' => $data['pswd'],
                'msg' => $data['message'],
                'mobile' => $data['mobile'],
                'needstatus' => true,
                'product' => '',
                'extno' => '',
            ],
        ];

        //config('sms.chuanglan.smsSendUrl')
        $promise = HttpClient::i()->request('POST', $data['smsSendUrl'], $request);
        $result = $promise->getBody()->getContents();
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


    /**
     * 获取创蓝主账户、子账户信息
     *
     * @param array $data
     * @return array
     */
    public function fetchAccount($data = [])
    {
        //logInfo('chuanglan', ['data' => $data, 'account' => config('sms.chuanglan.' . $data['smsSign'] . '_smsAccount')]);
        //创蓝账户
        if (isset($data['smsSign']) && $data['smsSign'] != 'sudaizhijia') //非速贷之家账户
        {
            $data['account'] = config('sms.chuanglan.' . $data['smsSign'] . '_smsAccount');
            $data['pswd'] = config('sms.chuanglan.' . $data['smsSign'] . '_smsPassword');
            $data['smsSendUrl'] = config('sms.chuanglan.' . $data['smsSign'] . '_smsSendUrl');

        } else //速贷之家账户
        {
            $data['account'] = config('sms.chuanglan.smsAccount');
            $data['pswd'] = config('sms.chuanglan.smsPassword');
            $data['smsSendUrl'] = config('sms.chuanglan.smsSendUrl');
        }

        //logInfo('sms-chuanglan', ['data' => $data]);
        return $data ? $data : [];
    }

}
