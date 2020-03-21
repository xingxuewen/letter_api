<?php

namespace App\Models\Chain\UserVip\Privilege;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ToolsFactory;
use App\Models\Factory\UserVipFactory;

/**
 * 工具点击流水统计
 *
 * Class CreateDataToolsLogAction
 * @package App\Models\Chain\Apply\ToolsApply
 */
class CreateDataPrivilegeLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '插入流水出错', 'code' => 10002);

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
        if ($this->createDataPrivilegeLog($this->params) == true) {
            return $this->params;
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function createDataPrivilegeLog($params)
    {
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($params['userId']);
        //获取渠道信息
        $params['delivery'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //流水记录
        $res = UserVipFactory::createDataUserVipPrivilegeLog($params);

        return $res;
    }
}