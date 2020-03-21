<?php

namespace App\Models\Chain\Sms\Register;

use App\Helpers\Generator\TokenGenerator;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Sms\SmsService;
use App\Models\Chain\Sms\Register\PutValueToCacheAction;
use App\Strategies\SmsStrategy;

class SendRegisterSmsAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '短信验证码下发已超上限', 'code' => 3);
    protected $randoms;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 发送注册短信
     */
    public function handleRequest()
    {
        if ($this->sendRegisterSms($this->params) == true) {
            $this->setSuccessor(new PutValueToCacheAction($this->params, $this->randoms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 发送注册短信
     */
    private function sendRegisterSms($params)
    {
        #生成四位数字短信验证码
        $code = mt_rand(1000, 9999);
        #组织短信验证码内容
        $data['mobile'] = $params['mobile'];
        $data['smsSign'] = $params['smsSign'];
        $data['channel_id'] = $params['channel_id'];
        $data['channel_title'] = $params['channel_title'];
        $data['channel_nid'] = $params['channel_nid'];
        $data['deviceId'] = isset($params['deviceId']) ? $params['deviceId'] : '';
        if (isset($params['smsSign']) && $params['smsSign'] != 'sudaizhijia') //非速贷之家短信
        {
            $data['message'] = "验证码：{$code}，此验证码十分钟后失效，请勿泄露给他人";

        } else //速贷之家短信
        {
            $data['message'] = "验证码：{$code}，请勿泄露。关注官方微信“速贷之家官微”";
        }
        $data['code'] = $code;
        //签名
        $data['sign'] = SmsStrategy::getSmsSignByAppname($data);
        #调取发送方法
        $re = SmsService::i()->to($data);
        #生成32位随机字符串
        $random = [];
        $random['sign'] = TokenGenerator::generateToken();

        $this->randoms = $random;
        $this->params['code'] = $code;

        if (!$re) {
            return false;
        }

        return true;
    }


}
