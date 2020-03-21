<?php

namespace App\Services;

/**
 * 外部Http Service服务调用
 */
class AppService
{

    /**
     * Instantiate a new Controller instance.
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai'); //时区配置
    }

    private static $serve;

    public static function o($config = [])
    {
        if (!(self::$serve instanceof static)) {
            self::$serve = new static($config);
        }

        return self::$serve;
    }

    // 新版接口
    const API_URL = PRODUCTION_ENV ? 'https://api.sudaizhijia.com' : 'https://uat.api.sudaizhijia.com';
    // H5域名
    const M_URL = PRODUCTION_ENV ? 'http://m.sudaizhijia.com' : 'https://uat.m.sudaizhijia.com';
    //新h5域名
    const H5_URL = PRODUCTION_ENV ? 'http://h5.sudaizhijia.com' : 'http://uat.h5.sudaizhijia.com';
    // 活动域名
    const EVENT_URL = PRODUCTION_ENV ? 'http://event.sudaizhijia.com' : 'http://test.event.sudaizhijia.com';
    // Web网站
    const WEB_URL = PRODUCTION_ENV ? 'http://www.sudaizhijia.com' : 'http://test.www.sudaizhijia.com';
    // 旧版接口
    const MAPI_URL = PRODUCTION_ENV ? 'http://mapi.sudaizhijia.com' : 'http://test.mapi.sudaizhijia.com';

    // 七牛存储根目录
    const ENV_QINIU_PATH = PRODUCTION_ENV ? 'production/' : 'test/';

    // OpenSNS域名
    const SNS_URL = PRODUCTION_ENV ? 'https://sns.sudaizhijia.com/m/index.php' : 'https://uat.sns.sudaizhijia.com/m/index.php';
//    const SNS_URL = PRODUCTION_ENV ? '' : 'http://dd.opensns.com/m/index.php';

    //openSNS域名
    const SNS_API_URL = PRODUCTION_ENV ? 'https://sns.sudaizhijia.com' : 'http://uat.sns.sudaizhijia.com';

    //易宝回调
    //回调地址
    const YIBAO_CALLBACK_URL = PRODUCTION_ENV ? 'https://api.sudaizhijia.com' : 'https://uat.api.sudaizhijia.com';

    //同步
    const API_URL_YIBAO_SYN = '/v1/callback/payment/yibao/syncallbacks?type=';

    //异步
    const API_URL_YIBAO_ASYN = '/v1/callback/payment/yibao/asyncallbacks?type=';

    // 芝麻API
    const ZHIMA_API_URL = 'https://zmopenapi.zmxy.com.cn/openapi.do';

    //汇聚支付回调
    //回调地址
    const HUIJU_CALLBACK_URL = PRODUCTION_ENV ? 'https://api.sudaizhijia.com' : 'https://uat.api.sudaizhijia.com';

    //同步
    const API_URL_HUIJU_SYN = '/v1/callback/payment/huiju/syncallbacks';

    //异步
    const API_URL_HUIJU_ASYN = '/v1/callback/payment/huiju/asyncallbacks';
    //异步 - 汇聚快捷支付 by xuyj
    const API_URL_HUIJU_QUICK_ASYN = '/v1/callback/payment/huiju/asyncallbacks_quick';
    //异步
    const API_URL_HUIJU_WECHAT_ASYN = '/v1/callback/payment/huiju/asyncallbacks_wechat';

    //idfa撞库地址
    const API_IDFA_TICK_URL = PRODUCTION_ENV ? 'http://tick.sudaizhijia.com' : 'http://uat.tick.sudaizhijia.com';

}
