<?php
namespace App\Models\Chain\ShadowSms\Sms\Register;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserFactory;

class CheckUserAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户是否存在检测出错', 'code' => 1);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.判断用户是否存在
     */
    public function handleRequest()
    {
        if ($this->checkUserData($this->params) == true) {
            #用户存在 直接发短信
            $this->setSuccessor(new SendRegisterSmsAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            #用户不存在  创建用户数据
            $this->setSuccessor(new CreateUserAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
    }

    /**
     * @param $params
     * 传值判断  不能高于用户账户额度
     */
    private function checkUserData($params)
    {
        $mobile  = $params['mobile'];
        #判断手机号是否存在在用户表中，如果不存在的话则直接将其添加进用户主表
        $user = UserFactory::fetchUserByMobile($mobile);
        if($user) {
            return true;
        }else {
            return false;
        }
    }


}
