<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;

class CreateProductApplyLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '创建流水失败！', 'code' => 10008);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 创建申请产品流水
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createProductApplyLog($this->params) == true) {
            $this->setSuccessor(new UpdateProductCountAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建申请产品流水
     * @param $params
     * @return bool
     */
    private function createProductApplyLog($params)
    {
        $productId = $params['productId'];
        $userId = $params['userId'];

        //单个产品点击立即申请流水统计

        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        if (isset($params['is_nothing']) && $params['is_nothing'] == 1)
        {
            //获取产品信息
            $productArr = ProductFactory::fetchProduct($productId);
        } else {
            //获取产品信息
            $productArr = ProductFactory::fetchProductname($productId);
        }

        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);

        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        //判断是否是vip产品
        $params['is_vip_product'] = ProductFactory::checkIsVipProduct($params);

        if (empty($userArr) || empty($productArr) || empty($deliveryArr) || empty($deliveryId)) {
            return false;
        }

        // 新版埋点在 gaea 服务
        if (isset($params['_skip_apply_log']) && $params['_skip_apply_log'] == 1) {
            return true;
        }

        $res = DeliveryFactory::createProductApplyLog($userId, $userArr, $productArr, $deliveryArr, $params);

        return $res;
    }
}