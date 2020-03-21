<?php

namespace App\Models\Chain\QuickLogin;

use App\Helpers\Logger\SLogger;
use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\QuickLogin\UpdateLoginTimeAction;
use App\Strategies\SmsStrategy;
use DB;
use Cache;
use SimpleSoftwareIO\QrCode\DataTypes\SMS;

class CheckCodeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '验证码输入不正确！', 'code' => 9000);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     * 第二步:验证码是否正确
     * @return array
     */
    public function handleRequest()
    {
        if ($this->checkCode($this->params['mobile'], $this->params['code'], $this->params['sign']) == true) {
            $userAuth = UserFactory::fetchUserByMobile($this->params['mobile']);
            $this->params['sd_user_id'] = $userAuth['sd_user_id'];
            $this->setSuccessor(new UpdateLoginTimeAction($this->params));
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
        //验证code&sign 【5分钟有效期】
        $registerKeys = SmsStrategy::getCodeKeyAndSignKey($mobile, 'register_v1');
        $codeKey = $registerKeys['codeKey'];
        $signKey = $registerKeys['signKey'];

        //添加5分钟验证码日志
        logInfo('login_five_code_' . $mobile, [
            'code' => $code, 'sign' => $sign, 'redis_code' => Cache::get($codeKey),
            'redis-sign' => Cache::get($signKey)]);

        #检查code以及sign
        $code_cache_key = 'login_phone_code_' . $mobile;
        $sign_cache_key = 'login_random_' . $mobile;

        //添加登录验证码日志
        logInfo('login_one_code_' . $mobile, [
            'code' => $code, 'sign' => $sign, 'redis_code' => Cache::get($code_cache_key),
            'redis-sign' => Cache::get($sign_cache_key)]);


//        if (Cache::has($code_cache_key) && Cache::has($sign_cache_key)) {
//            if (Cache::get($code_cache_key) == $code && Cache::get($sign_cache_key) == $sign) {
//                //修改code标识status转为0
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
