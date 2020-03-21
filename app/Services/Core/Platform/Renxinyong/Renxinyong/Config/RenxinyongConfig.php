<?php
namespace App\Services\Core\Platform\Renxinyong\Renxinyong\Config;

class RenxinyongConfig
{
    //测试线地址
    const TEST_URL = 'http://47.96.37.141:8730/bycx-rece-service/aSysLoginThird/autoReg/sdzj';
    //正式线地址
    const FORMAL_URL = 'http://mld-app.boyacx.com:8080/bycx-rece-service/aSysLoginThird/autoReg/sdzj';
    //地址
    const URL = PRODUCTION_ENV ? RenxinyongConfig::FORMAL_URL : RenxinyongConfig::TEST_URL;

    //渠道号 速贷之家(app)：36500376
    const CHANNEL_CODE = '36500376';
}