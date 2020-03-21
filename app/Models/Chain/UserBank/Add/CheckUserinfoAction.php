<?php
namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserBank\Add\CheckUserBankAction;
use App\Strategies\UserIdentityStrategy;

/**
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 * 1.验证用户信息，获取用户信息
 */
class CheckUserinfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '用户未认证！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }



    /**
     * 验证用户信息，获取用户信息
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkUserinfo($this->params) == true)
        {
            $this->setSuccessor(new CheckUserBankAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }


    /**
     * 验证用户信息，获取用户信息
     * @param $params
     * @return bool
     */
    private function checkUserinfo($params)
    {
        //实名步骤
        $params['step'] = UserIdentityStrategy::getRealnameStep($params);
        ////获取用户认证信息
        $userinfo = UserIdentityFactory::fetchIdcardAuthenInfoByStatus($params);
        //用户未认证
        if (!$userinfo) {
            return false;
        }
        $this->params['realname'] = $userinfo['realname'];
        $this->params['certificate_no'] = $userinfo['certificate_no'];

        return true;
    }
}
