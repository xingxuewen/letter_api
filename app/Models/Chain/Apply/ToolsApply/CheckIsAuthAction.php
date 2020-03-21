<?php

namespace App\Models\Chain\Apply\ToolsApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ToolsFactory;

/**
 * 验证是否需要登录
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Spread\Apply
 */
class CheckIsAuthAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '登录认证！', 'code' => 401);

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
        if ($this->checkIsAuth($this->params) == true) {
            $this->setSuccessor(new FetchToolsUrlAction($this->params));
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
    public function checkIsAuth($params = [])
    {
        //需要登录 若用户没有登录 返回false
        if ($params['tools']['is_login'] == 1 && empty($params['userId'])) //需要登录
        {
            return false;
        }

        return true;
    }
}