<?php

namespace App\Models\Chain\UserIdentity\MegviiAlive;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Models\Chain\UserIdentity\MegviiAlive\CreateUserAliveLogAction;
use App\Services\Core\Validator\FaceId\Megvii\MegviiService;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 1.face++验证活体
 */
class AliveVerifyAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => 'megvii验证活体失败', 'code' => 10001);

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
        if ($this->aliveVerify($this->params) == true) {
            $this->setSuccessor(new CreateUserAliveLogAction($this->params));
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
    private function aliveVerify($params = [])
    {
        $res = MegviiService::verify($params);

        //当请求失败时才会返回此字符串，具体返回内容见后续错误信息章节，否则此字段不存在。
        if (isset($res['error'])) {
            return false;
        }

        if (isset($res['result_code']) && $res['result_code'] != '1000') //1000 SUCCESS
        {
            return false;
        }

        //对应2.0.6中的image_env
        $this->params['image_env'] = $res['images']['image_best'] ? $res['images']['image_best'] : '';

        //返回json串
        $this->params['alive'] = $res;
        return true;

    }

}
