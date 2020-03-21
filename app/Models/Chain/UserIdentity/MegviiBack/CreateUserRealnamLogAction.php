<?php

namespace App\Models\Chain\UserIdentity\MegviiBack;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Strategies\UserIdentityStrategy;
use App\Models\Chain\UserIdentity\MegviiBack\UpdateUserRealnameAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.记实名认证流水
 */
class CreateUserRealnamLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '请您扫描有效期内证件', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 3.记实名认证流水
     */
    public function handleRequest()
    {
        if ($this->createUserRealnamLog($this->params) == true) {
            $this->setSuccessor(new UpdateUserRealnameAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 3.记实名认证流水
     */
    private function createUserRealnamLog($params = [])
    {
        // 记流水
        $params['type'] = 'faceid';
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;

        //判断身份证期限
        $time = date('Y-m-d', time());
        if ($params['card_endtime'] <= $time) {
            return false;
        }

        return UserIdentityFactory::createUserRealnameLog($params);

    }

}
