<?php

namespace App\Models\Chain\Login;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\AuthFactory;
use App\Models\Chain\Login\CheckUserExistAction;
use App\Models\Orm\UserAuthLogin;
use DB;

class SaveUserInfoAction extends AbstractHandler
{

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*  第一步:保存用户信息
     * @return array
     */

    public function handleRequest()
    {
        echo 111;die;
        //保存用户登录信息并获取id 责任链完成后更新user_id
        $user_auth_login_id = $this->saveUserInfo($this->params);
//        dd($user_auth_login_id);
        $this->params['user_auth_login_id'] = $user_auth_login_id;

        $this->setSuccessor(new CheckUserExistAction($this->params));
        return $this->getSuccessor()->handleRequest();
    }

    /**
     * 保存用户信息
     * @param $params
     * @return int
     */
    private function saveUserInfo($params)
    {
        return AuthFactory::createUserAuthLogin($params);
    }

}
