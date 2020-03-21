<?php

namespace App\Models\Chain\Promotion\Login;

use App\Models\Factory\UserFactory;
use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use DB;
use App\Models\Chain\Promotion\Login\UpdateLoginTimeAction;

/**
 *
 * Class CheckUserLockAction
 * @package App\Models\Chain\Promotion\Login
 */
class CheckUserLockAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '该用户被锁定，请联系速贷之家官方客服!', 'code' => 403403);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*  第二步:检查用户是否被锁定
     * @return array
     */

    public function handleRequest()
    {
        if ($this->checkUserLock($this->params['mobile']) == true)
        {
            $userAuth = UserFactory::fetchUserByMobile($this->params['mobile']);
            $this->params['sd_user_id'] = $userAuth['sd_user_id'];
            $this->setSuccessor(new UpdateLoginTimeAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 查数据库确认用户信息是否被锁定
     */
    private function checkUserLock($mobile)
    {
        return UserAuth::select("mobile")->where('mobile', '=', $mobile)->where('is_locked','=',0)->first();
    }

}
