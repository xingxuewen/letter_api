<?php
namespace App\Services\Core\PlatformButt\Fangsiling\Fangsiling\Config;
/**
 * Created by PhpStorm.
 * User: php
 * Date: 18-12-3
 * Time: 上午10:37
 */

class Config{

    const TEST_URL = 'http://testweb.51kaixinhua.com/test/thirdparty/api/filterByMobileMD5';
    //正式线地址
    const FORMAL_URL = 'http://zujin.58fangdai.com/thirdparty/api/filterByMobileMD5';
    //地址
    const URL = PRODUCTION_ENV ? Config::FORMAL_URL : Config::TEST_URL;


    const APP_ID='sdzj_api';
}