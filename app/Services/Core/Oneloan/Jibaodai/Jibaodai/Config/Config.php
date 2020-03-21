<?php

namespace App\Services\Core\Oneloan\Jibaodai\Jibaodai\Config;

/**
 * 吉宝贷
 * 环境配置
 *
 * Class Config
 * @package App\Services\Core\Platform\Jibaodai\Jibaodai\Config
 */
class Config
{
    //测试地址
    const TEST_URL = 'http://112.74.36.206:8888/receive-v1/jibaodai/sync/receiver';
    //正式地址
    const FORMAL_URL = 'http://120.78.241.111/jibaodai/sync/receiver';

    //调用地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    //渠道号
    const CHANNEL_NO = PRODUCTION_ENV ? '1330001' : '1330001';
    //密钥
    const SECRET_KEY = PRODUCTION_ENV ? 'zhijie2018' : 'zhijie2018test';

}