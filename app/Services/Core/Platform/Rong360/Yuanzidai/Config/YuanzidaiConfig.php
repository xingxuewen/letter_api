<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Platform\Rong360\Yuanzidai\Config;

class YuanzidaiConfig {

    //测试线地址
    const TEST_URL = 'https://fastopen.rong360.com/api/test/oauth/login';
    //正式线地址
    const FORMAL_URL = 'https://fastopen.rong360.com/api/yzd_sdzj/oauth/login';
    //地址
    const URL = PRODUCTION_ENV ? YuanzidaiConfig::FORMAL_URL : YuanzidaiConfig::FORMAL_URL;

}