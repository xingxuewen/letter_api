<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\Items\MemberProduct;

/**
 * 排除的产品id
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsExcludeProduct extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (empty($this->_params['excludeProduct'])) {
            return $this->_productIds;
        }

        $excludeProduct = (array) $this->_params['excludeProduct'];

        $ids = array_diff($this->_productIds, $excludeProduct);

        return $ids;
    }
}
