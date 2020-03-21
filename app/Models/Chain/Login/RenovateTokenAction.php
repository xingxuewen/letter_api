<?php

namespace App\Models\Chain\Login;

use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\AbstractHandler;
use App\Helpers\Generator\TokenGenerator;
use Cache;
use Carbon\Carbon;

class RenovateTokenAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '更新token失败!!', 'code' => 9003);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /*
     * 第六步:刷新用户token
     * @return array
     */
    public function handleRequest()
    {
        if ($this->renovateToken($this->params) == true)
        {
            $this->setSuccessor(new FetchUserInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 刷新用户token
     * @param $params
     * @return bool
     */
    private function renovateToken($params)
    {

        $user = UserFactory::getUserById($params['sd_user_id']);
        if ($user)
        {
            Cache::put('user_token_' . $params['sd_user_id'], $user, Carbon::now()->addDays(7));
            return true;
        }
        return false;
    }

}
