<?php

namespace App\Models\Chain\UserIdentity\VerifyCarrier;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserIdentity\VerifyCarrier\VerifyMobileInfo3CAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 身份证号已存在
 */
class CheckIsIdcardAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '身份证号已被占用！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 身份证号已存在
     */
    public function handleRequest()
    {
        if ($this->checkIsIdcard($this->params) == true) {
            $this->setSuccessor(new VerifyMobileInfo3CAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function checkIsIdcard($params = [])
    {
        $data['id_card_number'] = $params['idcard'];
        $data['userId'] = $params['userId'];
        $isIdcard = UserIdentityFactory::checkUseByIdCard($data);

        if ($isIdcard) {
            return false;
        }

        return true;
    }

}
