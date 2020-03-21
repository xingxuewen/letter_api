<?php

namespace App\Models\Chain\Login;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Login\CheckUserExistAction;
use App\Models\Chain\Login\SaveUserInfoAction;

class DoLoginHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 第一步:保存用户登录信息
     * 第二步:检查该用户信息是否存在
     * 第三步:检查用户是否被锁定
     * 第四步:检查该用户密码是否正确
     * 第五步:更新用户最后登录时间
     * 第六步:刷新用户token
     * 第七步:返回用户信息以及性别、真实性别
     * 第八步:插入用户登录日志表user_id
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        echo 11;die;
        $this->setSuccessor(new SaveUserInfoAction($this->params));
        return $this->getSuccessor()->handleRequest();
    }

}
