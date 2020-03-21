<?php

namespace App\Models\Chain\Quickloan\Quickloan;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\QuickloanFactory;
use App\Models\Chain\Quickloan\Quickloan\UpdateConfigCountAction;

/**
 * 记录流水
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Quickloan\Quickloan
 */
class CreateDataLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '统计流水失败！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 记录流水
     *
     * @return mixed
     */
    public function handleRequest()
    {
        if ($this->createDataLog($this->params) == true) {
            $this->setSuccessor(new UpdateConfigCountAction($this->params));
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
    public function createDataLog($params = [])
    {
        $userId = $params['userId'];
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        $res = QuickloanFactory::createDataQuickloanConfigLog($params,$deliveryArr);

        return $res;
    }
}