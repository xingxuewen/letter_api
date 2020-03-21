<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\Items\MemberProduct;

/**
 *
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsVipProduct extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (!isset($this->_params['vipProduct']) || intval($this->_params['vipProduct']) === 0) {
            return $this->_productIds;
        }

        $memberProduct = (new MemberProduct())->getData();

        if (empty($memberProduct)) {
            return [];
        }

        $ids = array_intersect($this->_productIds, $memberProduct);

        return $ids;
    }
}
