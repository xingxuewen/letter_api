<?php
namespace App\Helpers;

use App\Redis\RedisClientFactory;

class Token
{
    const CACHE_KEY = 'helpers_token_';

    const TYPE_COMMON = 1;
    const TYPE_ONCE = 2;
    const TYPE_TEMP = 3;

    protected $_expire = [
        self::TYPE_COMMON => 2592000,
        self::TYPE_ONCE => 86400,
        self::TYPE_TEMP => 300,
    ];

    protected $_encrypt = null;
    protected $_redis = null;
    protected $_data = [];

    public function __construct()
    {
        $this->_encrypt = new Encrypt();
        $this->_redis = RedisClientFactory::get();
    }

    /**
     * @param string $key
     * @param array $val
     * @param int $type
     * @param null $expire
     * @return string
     */
    public function create(string $key, $val, $type = self::TYPE_COMMON, $expire = null)
    {
        $expire = $expire ?? $this->_expire[$type];
        $cacheKey = sprintf("%s#%s#%s", self::CACHE_KEY, $type, $key);
        $rand = mt_rand(1000, 9999);

        $data = [
            'rand' => $rand,
            'time' => time(),
            'type' => $type,
            'key' => $key,
            'cache_key' => $cacheKey,
            'expire' => $expire,
            'use_sign' => '',
            //'ip' => Utils::ipAddress(),
        ];

        $k = $this->_encrypt->encode(base64_encode(json_encode($data)));

        $this->_redis->setex($cacheKey, $expire, serialize($val));

        return $k;
    }

    public function createOnce(string $key, $val, $expire = null)
    {
        return $this->create($key, $val, self::TYPE_ONCE, $expire);
    }

    public function createTemp(string $key, $val, $expire = null)
    {
        return $this->create($key, $val, self::TYPE_TEMP, $expire);
    }

    public function verify($key)
    {
        $this->_data = [];

        $res = $this->_encrypt->decode($key);
        $res = empty($res) ? '' : base64_decode($res);
        $res = empty($res) ? [] : @json_decode($res, true);

        if (empty($res['cache_key'])) {
            return [];
        }

        $data = $this->_redis->get($res['cache_key']);

        if (!empty($data)) {
            switch ($res['type']) {
                case self::TYPE_ONCE:
                    $this->_redis->del($res['cache_key']);
                    break;

                case self::TYPE_COMMON:
                case self::TYPE_TEMP:

                    break;
                default:
                    break;
            }
        }

        if (time() > (intval($res['time']) + intval($res['expire']))) {
            return [];
        }

        //var_dump($data);exit;
        $data = empty($data) ? null : @unserialize($data);

        return $data;
    }
}
