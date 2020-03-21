<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 产品是否在显示时间
 *
 * @package App\Services\Lists\Filter\Items
 */
class InShowTime extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;
        $_ids = Product::getProductShowTimes($this->_productIds, $this->_user->terminalType);

        if (!empty($_ids)) {
            $ids = array_diff($ids, $_ids);
        }

        return $ids;
    }
}
