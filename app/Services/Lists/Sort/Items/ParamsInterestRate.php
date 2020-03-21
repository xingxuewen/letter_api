<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 利率最低
 *
 * Class Common
 * @package App\Services\Lists\Sort\Items
 */
class ParamsInterestRate extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($this->_params['productType']) || $this->_params['productType'] != Base::SORT_INTEREST_RATE) {
            return $productIds;
        }

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = $productsInfo[$id]['month_rate'];
            $ids[] = $id;
        }

        array_multisort($sort, SORT_ASC,SORT_NUMERIC, $ids);

        return $ids;
    }
}
