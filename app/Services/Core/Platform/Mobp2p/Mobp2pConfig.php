<?php

namespace App\Services\Core\Platform\Mobp2p;

/**
 * 手机贷配置文件
 */
class Mobp2pConfig
{
    const  DES_KEY = 'u8c9kToN';
    // 测试环境
    //const $clientPublicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQABMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCN8QH9sJzg0QLqDe5b6sbduJqAXqDUgkLTPjS+At249aeCjG5AV/5n4mLdLxGEZ+bILmfc/DKk8nBcttyccbyEiWQ912+X3gwdK+8FHo9xYBtI5Y2+aIHVQ1hMQ85MGcsEJIQ6cHn6nJcz298vuJReZJGvzHTJaNx4ZWnVPDgivwIDAQAB';
    // 生成环境
    const CLIENT_PUBLIC_KEY = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8K7T84gTf0E1dIH1rB1KrzlEC/rtThdD8hzfS+hYzilY6YzQ7/umsXmpYnsxVPqcva0LKod4/rAJbfwFBG+LAGEZoDtm4HFt8CaPIKCt2c81LlJo9r4wtodLTgIpf4AL0A0VT3rA0RJVD7563aiJYdCA9VEYuTqw56cQKsl8PbQIDAQAB';
    //第三方接入标识
    const CHANNEL = 'sudaizj-llcs';
    //第三方接入商编码
    const MERCHANTID = '201605123985';
    //测试线地址
    const UAT_URL = 'http://116.228.32.182:7070/wap/union/login';
    //正式线地址
    const URL = 'https://m.shoujidai.com/user/quickLogin';

    // 获取链接
    public static function getUrl() {
        return PRODUCTION_ENV ? static::URL : static::UAT_URL;
    }

}