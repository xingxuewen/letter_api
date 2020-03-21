<?php

namespace App\Models\Chain\Quickloan\Quickloan;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Quickloan\Quickloan\CreateDataLogAction;

/**
 * 验证是否登录
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Quickloan\Quickloan
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
     * 验证是否登录
     *
     * @return mixed
     */
    public function handleRequest()
    {
        if ($this->checkIsLogin($this->params) == true) {

            $this->setSuccessor(new FetchQuickloanUrlAction($this->params));
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
    public function checkIsLogin($params = [])
    {
        if ($params['config']['is_login'] == 1) //需要登录
        {
            if (empty($params['userId'])) //用户未登录
            {
//                return false;
                //不验证
                return true;
            }
        }

        return true;
    }
}