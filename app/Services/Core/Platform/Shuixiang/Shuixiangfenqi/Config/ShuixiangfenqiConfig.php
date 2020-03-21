<?php
namespace App\Services\Core\Platform\Shuixiang\Shuixiangfenqi\Config;

class ShuixiangfenqiConfig
{
    //测试线地址
    const TEST_URL = 'http://106.15.126.217:8092/beadwalletloanapp/sxy/sudai/checkuser.do';
    //正式线地址
    const FORMAL_URL = 'https://yl.beadwallet.com/beadwalletloanapp/sxy/sudai/checkuser.do';
    //地址
    const URL = PRODUCTION_ENV ? ShuixiangfenqiConfig::FORMAL_URL : ShuixiangfenqiConfig::FORMAL_URL;


}