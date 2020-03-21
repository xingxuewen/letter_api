<?php

namespace App\Services\Core\Oneloan\Rongdai\Rongdai\Config;

/**
 * 融贷网配置
 *
 * Class Config
 * @package App\Services\Core\Platform\Rongdai\Rongdai\Config
 */
class Config
{
    //测试环境
    const TEST_URL = 'http://dev.oc.rongd.net/Center/pushOrder';
    //正式环境
    const FORMAL_URL = 'http://oc.rongd.net/Center/pushOrder';
    //地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    //渠道ID
    const CHANNEL_ID = PRODUCTION_ENV ? 23 : 23;
    //标识ID
    const BIAOSHI_ID = PRODUCTION_ENV ? 1 : 1;

    //加密密钥
    const ENCRYPT_SECRET = PRODUCTION_ENV ? '6wBYrlwCXBbRCu8igQczS7VTZkxGgdlm' : '6wBYrlwCXBbRCu8igQczS7VTZkxGgdlm';
}