<?php

namespace App\Models\Chain\Sms\Register;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\SmsFactory;
use App\Strategies\SmsStrategy;
use Illuminate\Support\Facades\Cache;

class PutValueToCacheAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '存短信信息进cache失败！', 'code' => 4);
    private $params = array();
    protected $randoms;

    public function __construct($params, $randoms)
    {
        $this->params = $params;
        $this->randoms = $randoms;
    }

    /**
     * @return mixed]
     * 存短信信息进cache
     */
    public function handleRequest()
    {
        if ($this->putValueToCache($this->params, $this->randoms) == true) {
            return $this->randoms;
        } else {
            return $this->error;
        }
    }

    /**
     * 存短信信息进cache
     *
     * @param $params
     * @param $randoms
     * @return bool
     */
    private function putValueToCache($params, $randoms)
    {
        //以前代码
        #存储验证码
        $codeCache = SmsFactory::putSmsCodeToCache('login_phone_code_' . $params['mobile'], $params['code']);
        #存储32位随机字符串
        $signCache = SmsFactory::putSmsCodeToCache('login_random_' . $params['mobile'], $randoms['sign']);

        //redis存储验证码，返回日志 1分钟有效
        logInfo('sd_sms_cache_get_one_res_' . $params['mobile'], ['code' => Cache::get('login_phone_code_' . $params['mobile']), 'sign' => Cache::get('login_random_' . $params['mobile'])]);

        // 注册 5分钟有效期key
        $registerKeys = SmsStrategy::getCodeKeyAndSignKey($params['mobile'], 'register_v1');
        // 注册 1分钟有效期key
        $registerExpireKeys = SmsStrategy::getCodeKeyAndSignKey($params['mobile'], 'register_expire');

        #存储验证码 【5分钟有效期】
        $fiveCodeCache = SmsFactory::putSmsCodeToCache($registerKeys['codeKey'], $params['code'], 300);
        #存储32位随机字符串 【5分钟有效期】
        $fiveCodeSignCache = SmsFactory::putSmsCodeToCache($registerKeys['signKey'], $randoms['sign'], 300);

        #存储验证码 【1分钟有效期】
        $codeExpireCache = SmsFactory::putSmsCodeToCache($registerExpireKeys['codeKey'], $params['code']);
        #存储32位随机字符串 【1分钟有效期】
        $signExpireCache = SmsFactory::putSmsCodeToCache($registerExpireKeys['signKey'], $randoms['sign']);

        //redis存储验证码，返回日志
        logInfo('sd_sms_cache_get_five_res_' . $params['mobile'], ['code' => Cache::get($registerKeys['codeKey']), 'sign' => Cache::get($registerKeys['signKey'])]);

        if ($codeCache && $signCache) {
            return true;
        }

        if ($fiveCodeCache && $fiveCodeSignCache && $codeExpireCache && $signExpireCache) {
            return true;
        }

        return false;
    }


}