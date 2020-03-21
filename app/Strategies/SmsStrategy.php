<?php

namespace App\Strategies;

use App\Constants\SmsConstant;
use App\Helpers\Logger\SLogger;
use App\Strategies\AppStrategy;

/**
 * 短信策略
 *
 * @package App\Strategies
 */
class SmsStrategy extends AppStrategy
{
    /**
     * @param string $inviteLink
     * @return string
     * 短信邀请
     */
    public static function getSmsContent($inviteLink = '')
    {
        return $smsContent = '邀请您加入速贷之家-极速贷款，上速贷之家。 ' . $inviteLink;
    }

    /**
     * @param $mobile
     * 获取 codeKey signKey
     * phone普通手机号，forgetpwd忘记密码，password修改密码，updatephone修改手机号，register注册
     *
     * @param string $mobile
     * @param string $type
     * @return mixed
     */
    public static function getCodeKeyAndSignKey($mobile = '', $type = '')
    {
        switch ($type) {
            case 'phone':
                //手机号
                $codeArr['codeKey'] = 'mobile_code_' . $mobile;
                $codeArr['signKey'] = 'mobile_random_' . $mobile;
                break;
            case 'forgetpwd':
                //忘记密码
                $codeArr['codeKey'] = 'forget_password_code_' . $mobile;
                $codeArr['signKey'] = 'forget_password_random_' . $mobile;
                break;
            case 'password':
                //修改密码
                $codeArr['codeKey'] = 'password_code_' . $mobile;
                $codeArr['signKey'] = 'password_random_' . $mobile;
                break;
            case 'updatephone':
                //修改手机号
                $codeArr['codeKey'] = 'update_mobile_code_' . $mobile;
                $codeArr['signKey'] = 'update_mobile_random_' . $mobile;
                break;
            case 'register':
                //注册
                $codeArr['codeKey'] = 'login_phone_code_' . $mobile;
                $codeArr['signKey'] = 'login_random_' . $mobile;
                break;
            case 'register_v1':
                //注册 【5分钟缓存有效期】
                $codeArr['codeKey'] = 'register_phone_code_' . $mobile;
                $codeArr['signKey'] = 'register_random_' . $mobile;
                break;
            case 'register_expire':
                //注册 【1分钟缓存有效期】
                $codeArr['codeKey'] = 'register_expire_phone_code_' . $mobile;
                $codeArr['signKey'] = 'register_expire_random_' . $mobile;
                break;
            default:
                //修改手机号
                $codeArr['codeKey'] = 'mobile_code_' . $mobile;
                $codeArr['signKey'] = 'mobile_random_' . $mobile;
                break;
        }
        return $codeArr;
    }

    /**
     * 发送短信内容
     * @param array $data
     * @return array
     */
    public static function getSmsMessage($data = [])
    {
        $shadowNid = isset($data['shadowNid']) ? $data['shadowNid'] : '';
        //短信内容
        switch ($shadowNid) {
            case 'shadow_jieqian360':
                $message = '关注官方微信“速贷之家官微”';
                break;
            case 'shadow_jieqianbao':
                $message = '关注官方微信“及速宝”';
                break;
            default:
                $message = '关注官方微信“速贷之家官微”';

        }

        $data['sms_message'] = $message;

        return $data;
    }

    /**
     * 安卓 - 速贷之家极简版 - 短信签名
     *
     * @param array $params
     * @return string
     */
    public static function getSmsSignByAppname($params = [])
    {
        $smsSign = isset($params['smsSign']) ? $params['smsSign'] : '';

        $sign = '【速贷之家】';
        if ($smsSign && !empty($smsSign) && in_array($smsSign, SmsConstant::ANDROID_B_SMS_SIGNS)) {
            $sms = SmsConstant::ANDROID_B_SMS_SIGN_KV;
            $sign = isset($sms[$smsSign]) ? $sms[$smsSign] : '【速贷之家】';
        }

        return $sign;
    }
}