<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 用户渠道
 *
 * @package App\Services\Lists\Filter\Items
 */
class UserDelivery extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        if ($this->_user->deliveryId > 0) {
            $ids = Product::checkProductIsInDelivery($ids, $this->_user->deliveryId );
            $ids = $ids ?: [];
        }

        return $ids;
    }
}
