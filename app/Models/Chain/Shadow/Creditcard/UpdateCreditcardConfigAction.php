<?php

namespace App\Models\Chain\Shadow\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\DataFactory;
use App\Models\Factory\DeliveryFactory;

/**
 * 插入流水表
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Spread\Apply
 */
class UpdateCreditcardConfigAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '统计失败！', 'code' => 10003);

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
        if ($this->updateSpreadConfig($this->params) == true) {
            $this->setSuccessor(new FetchInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param array $params
     * @return array|bool
     */
    public function updateSpreadConfig($params = [])
    {
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($params['userId']);
        //获取渠道信息
        $params['delivery'] = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //创建流水
        $log = DataFactory::createShadowDataCreditcardConfigLog($params);
        //修改总计数
        $clickCount = CreditcardFactory::updateShadowCreditcardConfigClickCount($params['configId']);

        if ($log && $clickCount) {
            return true;
        }

        return false;
    }
}