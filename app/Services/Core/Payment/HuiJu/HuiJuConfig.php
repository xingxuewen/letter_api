<?php

namespace App\Services\Core\Payment\HuiJu;

class HuiJuConfig
{
    //汇聚支付接口地址
    const HUIJU_URL = PRODUCTION_ENV ? 'https://www.joinpay.com' : 'https://www.joinpay.com';

    //版本号
    const HUIJU_VERSION = PRODUCTION_ENV ? '1.0' : '1.0';

    //商户编号
    const HUIJU_MERCHANTNO = PRODUCTION_ENV ? '888105100000721' : '888105100000721';

    //秘钥
    const HUIJU_SECRET = PRODUCTION_ENV ? '177929259c9db935ec92b356b9f08c97' : '177929259c9db935ec92b356b9f08c97';

    //交易币种 默认设置为 1(代表人民币)
    const HUIJU_CUR = PRODUCTION_ENV ? 1 : 1;

    //交易类型 支付宝 H5
    const HUIJU_FRPCODE = 'ALIPAY_H5';

    // 交易类型 调起汇聚微信小程序支付
    const HUIJU_FRPCODE_WECHAT = 'WEIXIN_APP2';


}