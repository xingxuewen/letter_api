<?php

namespace App\Constants;

use App\Constants\AppConstant;
use App\Services\AppService;
use Illuminate\Support\Facades\App;

/**
 * 帮助中心使用常量
 */
class HelpConstant extends AppConstant
{
    //信用&借款
    const HELP_CREDIT_LOAN = 'credit_loan';
    //速贷币
    const HELP_QUICK_MONEY = 'quick_money';
    //如何借款
    const HELP_HOW_LOAN = 'how_loan';

    //官方热线
    const HELP_OFFICIAL_HOTLINE = '4000390718';
    //官方QQ群
//    const HELP_OFFICIAL_QQ = '497701352';
//    const HELP_OFFICIAL_QQ_IOS_KEY = '953d07afa3874693a0cbd9f76fd528fc60f7e0aca00010b0ac608b010438fb03';
//    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'o3udeWOBqeEkd3ft_UMBPd4a4WfnQq1O';
//    const HELP_OFFICIAL_QQ_WEB_KEY = '953d07afa3874693a0cbd9f76fd528fc60f7e0aca00010b0ac608b010438fb03';

    //官方QQ群 第二版
//    const HELP_OFFICIAL_QQ = '167411270';
//    const HELP_OFFICIAL_QQ_IOS_KEY = '05a44737a6bfa0d80eb76889d45a20111adb8de168f68950aa05e9a23f0a0455';
//    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'tu6lcm7Ykur3TLRPEDw_rCMdjjzzuxNt';
//    const HELP_OFFICIAL_QQ_WEB_KEY = '05a44737a6bfa0d80eb76889d45a20111adb8de168f68950aa05e9a23f0a0455';


    //官方QQ群 第三版
//    const HELP_OFFICIAL_QQ = '531947670';
//    const HELP_OFFICIAL_QQ_IOS_KEY = '7322ab47dac26d2c4154b38936fd8ff745bb630d23f4fe35ebbe73652e3237d4';
//    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'uhzkd6G-_1LdM5kdRPRhXZOV7JON5KnW';
//    const HELP_OFFICIAL_QQ_WEB_KEY = '7322ab47dac26d2c4154b38936fd8ff745bb630d23f4fe35ebbe73652e3237d4';

    //官方QQ群 第四版
    const HELP_OFFICIAL_QQ = '562880903';
    const HELP_OFFICIAL_QQ_IOS_KEY = '6c39ba4bb85787fcf734a1c2136b6a125db57b53d7d3efdf9f4ef961c7f59a52';
    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'bzhXqMZpLyqCHiuwSdpfsQA6xT8XOe6U';
    const HELP_OFFICIAL_QQ_WEB_KEY = '6c39ba4bb85787fcf734a1c2136b6a125db57b53d7d3efdf9f4ef961c7f59a52';


    //协议中心
    const AGREEMENTS = [
        [
            'id' => 1,
            'name' => '《速贷之家用户注册协议》',
            'url' => AppService::API_URL . '/view/users/identity/use',
        ],
        [
            'id' => 2,
            'name' => '《速贷之家VIP会员服务协议》',
            'url' => AppService::API_URL . '/view/users/identity/membership',
        ],
        [
            'id' => 3,
            'name' => '《速贷之家信用检测授权协议》',
            'url' => AppService::API_URL . '/view/users/report/agreement',
        ],
        [
            'id' => 4,
            'name' => '《速贷之家个人身份认证协议》',
            'url' => AppService::API_URL . '/view/users/identity/agreement',
        ],
    ];

}

