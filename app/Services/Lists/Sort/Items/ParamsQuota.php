<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 额度最大
 *
 * @package App\Services\Lists\Sort\Items
 */
class ParamsQuota extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($this->_params['productType']) || $this->_params['productType'] != Base::SORT_QUOTA) {
            return $productIds;
        }

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = $productsInfo[$id]['avg_quota'];
            $ids[] = $id;
        }

        array_multisort($sort, SORT_DESC,SORT_NUMERIC, $ids);

        return $ids;
    }
}
