<?php
namespace App\Models\Chain\Apply\SpreadApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Apply\SpreadApply\CheckIsAuthenAction;

/**
 * 验证是否注册
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Spread\Apply
 */
class CheckIsLoginAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '请登录！', 'code' => 401);

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
        if ($this->checkIsLogin($this->params) == true) {
            $this->setSuccessor(new CheckIsAuthenAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param array $params
     * @return array|bool
     */
    public function checkIsLogin($params = [])
    {
        $config = $params['config'];

        //是否需要登录
        if ($config && $config['is_login'] == 1) //需要登录
        {
            //验证用户是否登录
            if(empty($params['userId']))
            {
                return false;
            }
        }

        return true;
    }
}