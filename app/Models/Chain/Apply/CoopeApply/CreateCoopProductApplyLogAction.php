<?php

namespace App\Models\Chain\Apply\CoopeApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\OneloanProductFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;

/**
 *
 * Class CreateProductApplyLogAction
 * @package App\Models\Chain\Apply\OneloanApply
 */
class CreateCoopProductApplyLogAction extends AbstractHandler
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
        if ($this->createProductApplyLog($this->params) == true) {
            return $this->params['url'];
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function createProductApplyLog($params)
    {
        $productId = $params['product']['product_id'];
        $userId = isset($params['userId']) ? $params['userId'] : '';
        //单个产品点击立即申请流水统计
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $productArr = OneloanProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);


        $res = DeliveryFactory::createCoopeProductApplyLog($userId, $userArr, $productArr, $deliveryArr);

        return $res;
    }
}