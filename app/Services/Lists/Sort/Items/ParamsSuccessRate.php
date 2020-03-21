<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 成功率最高
 *
 * @package App\Services\Lists\Sort\Items
 */
class ParamsSuccessRate extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($this->_params['productType']) || $this->_params['productType'] != Base::SORT_SUCCESS_RATE) {
            return $productIds;
        }

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = $productsInfo[$id]['success_rate'];
            $ids[] = $id;
        }

        array_multisort($sort, SORT_DESC,SORT_NUMERIC, $ids);

        return $ids;
    }
}
