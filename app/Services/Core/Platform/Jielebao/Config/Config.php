<?php
namespace App\Services\Core\Platform\Jielebao\Config;
/**
 * Created by PhpStorm.
 * User: php
 * Date: 18-10-25
 * Time: 下午8:05
 */

class Config{

    const TEST_URL = 'http://dtest1-www.wyoubt.com/frontend/web/interface-union-login/register';
    //正式线地址
    const FORMAL_URL = 'http://jlb.xinyongbt.com/frontend/web/interface-union-login/register';
    //地址
    const URL = PRODUCTION_ENV ? Config::FORMAL_URL : Config::TEST_URL;

//    const KEY=PRODUCTION_ENV ? '6SW3SLDKKSAS3AFJ': '7BFCF5C921231SDF';

    const QUDAO=PRODUCTION_ENV ? 'jlbao_sudailianhe_openlogin': 'xybt_ppd';
}