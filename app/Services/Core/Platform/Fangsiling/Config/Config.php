<?php
namespace App\Services\Core\Platform\Fangsiling\Config;
/**
 * Created by PhpStorm.
 * User: php
 * Date: 18-11-28
 * Time: 下午6:34
 */
class Config{

    const TEST_URL = 'http://testweb.51kaixinhua.com/test/thirdparty/api/login';
    //正式线地址
    const FORMAL_URL = 'http://zujin.58fangdai.com/thirdparty/api/login';
    //地址
    const URL = PRODUCTION_ENV ? Config::FORMAL_URL : Config::TEST_URL;


    const APP_ID='sdzj_api';
}