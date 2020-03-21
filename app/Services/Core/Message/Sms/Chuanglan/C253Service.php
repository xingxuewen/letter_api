<?php

namespace App\Services\Core\Message\Sms\Chuanglan;

use App\Services\Core\Sms\SmsService;
use App\Helpers\Http\HttpClient;
use App\Models\Orm\MessageLog;
use Log;

/**
 * 创蓝短信通道
 * Class ChuanglanService
 * @package App\Services\Core\Sms\Chuanglan
 */
class C253Service extends SmsService
{

    /**
     *
     * @param $data
     */
    public function send($data, $config = [])
    {
        if (!empty($config)) {
            $sign = $this->getSignMeg($data);
            $request = [
                'json' => [
                    'account' => $config['username'],
                    'password' => $config['password'],
                    'msg' => $sign . $data['message'] . '---',
                    'phone' => $data['mobile'],
                ],
            ];

            $promise = HttpClient::i()->request('POST', $config['url'], $request);
            $result = $promise->getBody()->getContents();
            $this->sendAfter($result, $data);
            return $result;
        }

        return [];
    }

    /**
     * 验签
     * @param array $data
     * @return string
     */
    public function getSignMeg($data = [])
    {
        switch ($data['shadowNid']) {
            case 'shadow_jieqian360':
                $sign = '【借钱360】';
                break;
            case 'shadow_jieqianbao':
                $sign = '【借钱宝】';
                break;
            default:
                $sign = '【借钱360】';
        }

        return $sign;
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
