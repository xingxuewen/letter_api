<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 主包可见的产品
 *
 * @package App\Services\Lists\Filter\Items
 */
class MainPackage extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = Product::getMainShowProductIds();

        if (empty($ids)) {
            return [];
        }

        $ids = array_intersect($ids, $this->_productIds);

        return $ids;
    }
}
