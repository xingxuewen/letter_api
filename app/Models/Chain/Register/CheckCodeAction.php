<?php

namespace App\Models\Chain\Register;

use App\Helpers\Logger\SLogger;
use App\Models\Factory\AuthFactory;
use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use App\Strategies\SmsStrategy;
use DB;
use Cache;

class CheckCodeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '请输入正确的验证码！', 'code' => 9000);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:验证码是否正确
     * @return array
     */
    public function handleRequest()
    {
        if ($this->checkCode($this->params['mobile'], $this->params['code'], $this->params['sign']) == true) {
            $this->setSuccessor(new CreateUserAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 检查验证码是否正确
     */
    private function checkCode($mobile, $code, $sign)
    {
        if (PRODUCTION_ENV === false && $code == '8888') {
            return true;
        }

        #查库
        $code_cache_key = 'login_phone_code_' . $mobile;
        $sign_cache_key = 'login_random_' . $mobile;

        //验证code&sign 【5分钟有效期】
        $registerKeys = SmsStrategy::getCodeKeyAndSignKey($mobile, 'register_v1');
        $codeKey = $registerKeys['codeKey'];
        $signKey = $registerKeys['signKey'];


        //添加1分钟注册验证码日志
        logInfo('register_one_code_' . $mobile, [
            'code' => $code, 'sign' => $sign, 'redis_code' => Cache::get($code_cache_key),
            'redis-sign' => Cache::get($sign_cache_key)]);

        //添加5分钟验证码日志
        logInfo('register_five_code_' . $mobile, [
            'code' => $code, 'sign' => $sign, 'redis_code' => Cache::get($codeKey),
            'redis-sign' => Cache::get($signKey)]);

//        if (Cache::has($code_cache_key) && Cache::has($sign_cache_key)) {
//            if (Cache::get($code_cache_key) == $code && Cache::get($sign_cache_key) == $sign) {
//                //修改code标识转为0
//                AuthFactory::updateUserAuthSMSCodeIsValidate($mobile);
//                return true;
//            }
//        }

        //判断验证码是否存在
        if ((Cache::has($code_cache_key) && Cache::has($sign_cache_key)) ||
            (Cache::has($codeKey) && Cache::has($signKey))
        ) {
            //验证验证码
            if ((Cache::get($code_cache_key) == $code && Cache::get($sign_cache_key) == $sign) ||
                (Cache::get($codeKey) == $code && Cache::get($signKey) == $sign)
            ) {
                //修改code标识转为0
                AuthFactory::updateUserAuthSMSCodeIsValidate($mobile);
                return true;
            }
        }

        return false;
    }

}
