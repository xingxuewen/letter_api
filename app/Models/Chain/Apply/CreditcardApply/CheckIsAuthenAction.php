<?php

namespace App\Models\Chain\Apply\CreditcardApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\Apply\CreditcardApply\FetchApplyUrlAction;

/**
 * 验证是否需要认证
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Spread\Apply
 */
class CheckIsAuthenAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '请实名认证！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkIsAuthen($this->params) == true) {
            $this->setSuccessor(new FetchApplyUrlAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     *
     * @param array $params
     * @return bool
     */
    public function checkIsAuthen($params = [])
    {
        $config = $params['config'];
        $this->params['is_realname'] = 0;

        //验证是否需要认证
        if ($config && $config['is_authen'] == 1 && $config['is_fake_realname'] == 0) //需要认证
        {
            $realname = UserIdentityFactory::fetchUserRealInfo($params['userId']);
            if (!$realname) //未实名
            {
                $this->params['is_realname'] = 0;
                return false;
            }
            $this->params['is_realname'] = 1;
        }

        return true;
    }
}