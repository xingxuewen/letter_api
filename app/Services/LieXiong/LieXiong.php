<?php
namespace App\Services\LieXiong;

use App\Redis\RedisClientFactory;
use GuzzleHttp\Client;

class LieXiong
{
    protected $_redis = null;

    protected $_http = null;

    protected $_host = '';

    protected $_appId = '';

    protected $_appKey = '';

    protected $_secret = '';

    protected $_notificationUrl = '';

    protected $_imageUrl = '';

    const CACHE_KEY_ACCESS_TOKEN = 'LieXiong_AccessToken';
    const CACHE_KEY_TOKEN = 'LieXiong_Token';

    const URI_SECRET_AUTHORIZE = '/secret/authorize';  // access token
    const URI_CARD_VIP_CARD    = '/card/vipCard';   // 会员卡列表查询接口
    const URI_USER_LOGIN       = '/user/login';     // 联合登录接口
    const URI_CARD_BUY_CARD    = '/card/buyCard';   // 烈熊收银台接口(购买会员)
    const URI_CARD_PAY_ORDER   = '/card/payOrder/%s'; // 用户购买会员订单查询接口
    const URI_CARD_USER_VIP_CARDS = '/card/userVipCards'; // 用户有效会员卡查询接口

    public function __construct()
    {
        $this->_imageUrl = env('LIEXIONG_IMAGE_URL');
        $this->_notificationUrl = env('LIEXIONG_NOTIFICATION_URL');
        $this->_host = env('LIEXIONG_HOST');
        $this->_appId = env('LIEXIONG_APPID');
        $this->_appKey = env('LIEXIONG_APPKEY');
        $this->_redis = RedisClientFactory::get();
        $this->_http = new Client();
        $this->_secret = base64_encode(hash('sha256', $this->_appId . $this->_appKey, true));
    }

    protected function _url($uri)
    {
        return $this->_host . $uri;
    }

    protected function _createAccessToken() : array
    {
        $url = $this->_url(self::URI_SECRET_AUTHORIZE);
        $data = [
            'appId' => $this->_appId,
            'secret' => $this->_secret,
        ];

        logInfo('LieXiong create accessToken input', [$url, $data]);

        $response = $this->_http->post($url, ['form_params' => $data]);

        if (empty($response)) {
            logError('LieXiong create accessToken output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong create accessToken output', $ret);

        $ret = @json_decode($ret, true);

        if (empty($ret['accessToken'])) {
            return [];
        }

        return $ret;
    }

    public function getAccessToken()
    {
        $accessToken = $this->_redis->get(self::CACHE_KEY_ACCESS_TOKEN);

        if (!empty($accessToken)) {
            return $accessToken;
        }

        $accessToken = $this->_createAccessToken();

        if (!empty($accessToken)) {
            $this->_redis->setex(self::CACHE_KEY_ACCESS_TOKEN, $accessToken['expiresIn'], $accessToken['accessToken']);
            return $accessToken['accessToken'];
        }

        return '';
    }

    /**
     * @param $phone
     * @param $userId
     * @param string $nickName
     * @param string $headImg
     * @return array
     */
    protected function _createToken($phone, $userId, $nickName = '', $headImg = '') : array
    {
        $accessToken = $this->getAccessToken();
        $url = $this->_url(self::URI_USER_LOGIN);
        $data = [
            'headers' => [
                'authorization' => $accessToken,
            ],
            'form_params' => [
                'phone' => $phone,
                'userId' => $userId,
                'nickName' => $nickName,
                'icon' => $headImg,
            ],
        ];

        logInfo('LieXiong userLogin input', [$url, $data]);

        try {
            $response = $this->_http->post($url, $data);
        } catch (\Exception $e) {
            logError('LieXiong userLogin output error', [$e->getMessage(), $e->getTraceAsString()]);
            return [];
        }


        if (empty($response)) {
            logError('LieXiong userLogin output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong userLogin output', $ret);

        $ret = @json_decode($ret, true);

        if (empty($ret['token'])) {
            return [];
        }

        return $ret;
    }

    /**
     * 联合登录接口
     *
     * @param $phone
     * @param $userId
     * @param string $nickName
     * @param string $headImg
     * @return string
     */
    public function userLogin($phone, $userId, $nickName = '', $headImg = '') : string
    {
        $cacheKey = implode('#', [self::CACHE_KEY_TOKEN, $userId]);
        $token = $this->_redis->get($cacheKey);

        if (!empty($token)) {
            return $token;
        }

        $token = $this->_createToken($phone, $userId, $nickName, $headImg);

        if (!empty($token)) {
            $this->_redis->setex($cacheKey, $token['expiresIn'], $token['token']);
            return $token['token'];
        }

        return '';
    }

    /**
     * 会员卡列表查询接口
     *
     * @return array
     */
    public function vipCard() : array
    {
        $accessToken = $this->getAccessToken();
        $url = $this->_url(self::URI_CARD_VIP_CARD);
        $data = [
            'headers' => [
                'authorization' => $accessToken,
            ],
        ];

        logInfo('LieXiong vipCard input', [$url, $data]);

        $response = $this->_http->get($url, $data);

        if (empty($response)) {
            logError('LieXiong vipCard output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong vipCard output', $ret);

        $ret = @json_decode($ret, true);

        if (!empty($ret)) {
            foreach ($ret as & $val) {
                $val['icon']['url'] = $this->_imageUrl . $val['icon']['key'];
            }
        }

        return $ret ?: [];
    }

    /**
     * 烈熊收银台接口(购买会员)
     *
     * @param string $phone
     * @param int $userId
     * @param array $orderInfo
     * @return array
     */
    public function buyCard(string $phone, int $userId, array $orderInfo) : array
    {
        $token = $this->userLogin($phone, $userId);
        $accessToken = $this->getAccessToken();
        $url = $this->_url(self::URI_CARD_BUY_CARD);
        $data = [
            'headers' => [
                'authorization' => $accessToken,
                'token' => $token,
            ],
            'form_params' => [
                'cardId' => $orderInfo['cardId'],
                //'price' => $orderInfo['price'] ?? 1,
                'partnerThirdOrderId' => $orderInfo['orderId'],
                'attach' => $orderInfo['attach'] ?? '',
                'successUrl' => $orderInfo['successUrl'] ?? '',
                'failUrl' => $orderInfo['failUrl'] ?? 1,
                'notificationUrl' => $this->_notificationUrl,
                'schema' => $orderInfo['schema'] ?? '',
                'buyCount' => $orderInfo['buyCount'] ?? 1,
            ],
        ];

        logInfo('LieXiong buyCard input', [$url, $data]);

        $response = $this->_http->post($url, $data);

        if (empty($response)) {
            logError('LieXiong buyCard output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong buyCard output', $ret);

        $ret = @json_decode($ret, true);

        if (empty($ret)) {
            return [];
        }

        return $ret;
    }

    /**
     * 用户购买会员订单查询接口
     *
     * @param string $orderId
     * @return array
     */
    public function payOrder(string $orderId) : array
    {
        $accessToken = $this->getAccessToken();
        $url = $this->_url(sprintf(self::URI_CARD_PAY_ORDER, $orderId));
        $data = [
            'headers' => [
                'authorization' => $accessToken,
            ],
        ];

        logInfo('LieXiong payOrder input', [$url, $data]);

        $response = $this->_http->post($url, $data);

        if (empty($response)) {
            logError('LieXiong payOrder output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong payOrder output', $ret);

        $ret = @json_decode($ret, true);


        return $ret ?: [];
    }

    /**
     * 用户有效会员卡查询接口
     *
     * @param string $userCardId
     * @param string $phone
     * @param int $userId
     * @return array
     */
    public function userVipCards(string $userCardId, string $phone, int $userId) : array
    {
        logInfo('LieXiong userVipCards params', [$userCardId, $phone, $userId]);

        $accessToken = $this->getAccessToken();
        $token = $this->userLogin($phone, $userId);
        $url = $this->_url(self::URI_CARD_USER_VIP_CARDS);
        $data = [
            'headers' => [
                'authorization' => $accessToken,
                'token' => $token,
            ],
        ];

        logInfo('LieXiong userVipCards input', [$url, $data]);

        $response = $this->_http->get($url, $data);

        if (empty($response)) {
            logError('LieXiong userVipCards output error');
            return [];
        }

        $ret = $response->getBody()->getContents();

        logInfo('LieXiong userVipCards output', $ret);

        $ret = empty($ret) ? [] : @json_decode($ret, true);

        if (empty($ret)) {
            return [];
        }

        $ret = array_column($ret, null, 'id');

        if (empty($ret[$userCardId])) {
            logError('LieXiong userVipCards card id is not exists');
            return [];
        }

        return $ret[$userCardId];
    }

    /**
     * 校验 sign
     *
     * @param $params
     * @return bool
     */
    public function validSign($params) : bool
    {
        $sign = $params['sign'] ?? '';

        if (empty($sign)) {
            return false;
        }

        unset($params['sign']);

        ksort($params);

        $str = implode('', $params);
        //var_dump($str);
        $sign1 = base64_encode(hash('sha256', $this->_appKey . $str, true));

        return $sign === $sign1;
    }
}