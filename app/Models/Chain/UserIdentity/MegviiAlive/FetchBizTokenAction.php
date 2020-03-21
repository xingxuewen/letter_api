<?php

namespace App\Models\Chain\UserIdentity\MegviiAlive;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Models\Chain\UserIdentity\MegviiAlive\CreateUserAliveLogAction;
use App\Services\Core\Validator\FaceId\Megvii\MegviiService;
use App\Strategies\UserIdentityStrategy;

/**
 * 1.获取biz_token
 *
 * Class FetchBizTokenAction
 * @package App\Models\Chain\UserIdentity\MegviiAlive
 */
class FetchBizTokenAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => 'megvii活体验证获取bit_token失败', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * face++验证活体
     */
    public function handleRequest()
    {
        if ($this->fetchBizToken($this->params) == true) {
            $this->setSuccessor(new AliveVerifyAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * face++验证活体
     */
    private function fetchBizToken($params = [])
    {
        //本接口仅支持FaceID MegLiveStill SDK 3.0及以上的版本来获取biz_token进行初始化
        $res = MegviiService::getAppBizToken($params);
        if (isset($res['error'])) //错误提示
        {
            return false;
        }

        //通过”App-GetBizToken“ API接口获取到的biz_token
        $this->params['biz_token'] = $res['biz_token'];
        return true;
    }

}
