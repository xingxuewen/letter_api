<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 根据用户所在地域过滤产品
 * Class Area
 * @package App\Services\Lists\Filter\Items
 */
class UserArea extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;
        //var_dump($this->_user->areaId, $ids);exit;
        if ($this->_user->areaId > 0) {
            $ids = Product::getProductByDevice($ids, $this->_user->areaId);
            //$ids = array_intersect($this->_productIds, $ids);
        }

        return $ids;
    }
}
