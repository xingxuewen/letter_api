<?php

namespace App\Services\Core\Sms;

use App\Constants\SmsConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Models\Factory\SmsFactory;
use App\Models\Orm\MessageLog;
use App\Services\AppService;
use App\Services\Core\Sms\Boshitong\BoshitongService;
use App\Services\Core\Sms\Changzhuo\ChangzhuoService;
use App\Services\Core\Sms\Chuanglan\ChuanglanService;
use App\Services\Core\Sms\Dahansantong\DahansantongService;
use App\Services\Core\Sms\Laiao\LaiaoEventService;
use App\Services\Core\Sms\Wwtl\WwtlService;
use App\Services\Core\Sms\Yimei\YimeiService;
use App\Services\Core\Sms\Jiguang\JiguangService;
use Carbon\Carbon;
use DB;
use App\Helpers\Utils;
use Cache;
use App\Helpers\Generator\TokenGenerator;
use Log;

class SmsService extends AppService
{
    public static $services;

    public static function i()
    {
        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 根据系统参数配置去选择短信通道
     * @param $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function to($data)
    {
        $ipNums = $this->limitIp();
        if ($ipNums >= 36) return false;
        $mobileNums = $this->limitMobile($data['mobile']);
        if ($mobileNums >= 6) return false;
        $uaNums = $this->limitUA();
        if ($uaNums >= 10000) return false;

        $config = SmsFactory::getSmsSystemConfig();

        //ios包只使用创蓝通道  19.03.08 由创蓝改为博士通
        if (isset($data['smsSign']) &&
            $data['smsSign'] != 'sudaizhijia' &&
            in_array($data['smsSign'], SmsConstant::IOS_SMS_SIGNS)
        ) {
            $data['sign'] = SmsConstant::IOS_SMS_SIGN_KV[$data['smsSign']];
            $config = 'boshitong';
        }

        $data['channel'] = $config;

        logInfo('send_sms_input', ['data' => $data]);

        switch ($config) {
            case 'chuanglan':
                $data['nid'] = $this->sendBefore($data);
                $re = ChuanglanService::i()->send($data);
                break;
            case 'changzhuo':
                $data['nid'] = $this->sendBefore($data);
                $re = ChangzhuoService::i()->send($data);
                break;
            case 'wwtl':
                $data['nid'] = $this->sendBefore($data);
                $re = WwtlService::i()->send($data);
                break;
            case 'yimei':
                $data['nid'] = $this->sendBefore($data);
                $re = YimeiService::i()->send($data);
                break;
            case 'dahansantong':
                $data['nid'] = $this->sendBefore($data);
                $re = DahansantongService::i()->send($data);
                break;
            case 'laiao':
                $data['nid'] = $this->sendBefore($data);
                $re = LaiaoEventService::i()->send($data);
                break;
            case 'jiguang':
                $data['nid'] = $this->sendBefore($data);
                $re = JiguangService::i()->send($data);
                break;
            case 'boshitong':
                $data['nid'] = $this->sendBefore($data);
                $re = BoshitongService::i()->send($data);
                break;
            default:
                $data['nid'] = $this->sendBefore($data);
                $re = ChangzhuoService::i()->send($data);
                break;

        }

        logInfo('send_sms_output', ['res' => $re, 'data' => $data]);

        return $re;
    }

    /**
     * 发送短信之前入库创建发送内容
     */
    protected function sendBefore($data)
    {

        $model = new MessageLog();
        $model->mobile = $data['mobile'];
        $model->nid = TokenGenerator::generateToken();
        $model->content = $data['message'];
        $model->send_time = date('Y-m-d H:i:s', time());
        $model->channel = $data['channel'];
        //1 验证码短信 2 通知短信
        $model->send_type = isset($data['send_type']) ? $data['send_type'] : 1;//验证码短信
        $model->auto = 0; //0为用户触发 1为系统发送
        $model->code = isset($data['code']) ? $data['code'] : '';
        $model->code_time = date('Y-m-d H:i:s', time() + 60);
        $model->send_ip = Utils::ipAddress();
        $model->channel_id = isset($data['channel_id']) ? $data['channel_id'] : '';//渠道id
        $model->channel_title = isset($data['channel_title']) ? $data['channel_title'] : '';//渠道名称
        $model->channel_nid = isset($data['channel_nid']) ? $data['channel_nid'] : '';//渠道号
        $model->device_id = isset($data['deviceId']) ? $data['deviceId'] : '';//渠道号
        $model->user_agent = UserAgent::i()->getUserAgent();
        $model->save();
        return $model->nid;
    }


    /**
     * 限制ip
     */
    protected function limitIp()
    {
        $ip = Utils::ipAddress();
        $key = 'sd_ip_value_' . $ip;
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }


    /**
     * 手机号限制
     */
    protected function limitMobile($mobile)
    {
        $key = 'sd_mobile_value_' . $mobile;
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }

    /**
     * UA限制
     */
    protected function limitUA()
    {
        $key = 'sd_ua_value_' . md5(UserAgent::i()->getUserAgent());
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, Carbon::now()->second(60 * 60 * 24));
        }
        return Cache::get($key);
    }
}
