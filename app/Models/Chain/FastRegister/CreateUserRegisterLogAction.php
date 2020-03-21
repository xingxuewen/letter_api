<?php

namespace App\Models\Chain\FastRegister;

use App\Models\Factory\AuthFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\DeliveryCount;
use \DB;

class CreateUserRegisterLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '用户快捷注册流水表入库失败！', 'code' => 111);
    protected $data;
    protected $user;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 创建用户用户快捷注册流水
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createUserRegisterLog($this->params) == true) {
            $this->setSuccessor(new FetchUserInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 用户快捷注册流水表存数据
     * @param $params
     * @return bool
     */
    private function createUserRegisterLog($params)
    {
        $channel = DeliveryCount::select('*')->where(['nid' => $params['channel_fr']])->first();
        if ($channel) {
            $channel = $channel->toArray();
            $params['channel_id'] = $channel['id'];
            $params['channel_title'] = $channel['title'];
            $params['channel_nid'] = $channel['nid'];
            AuthFactory::createUserRegisterLog($params);
        }

        return true;
    }
}
