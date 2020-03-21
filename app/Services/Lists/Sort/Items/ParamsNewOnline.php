<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 最新产品
 *
 * @package App\Services\Lists\Sort\Items
 */
class ParamsNewOnline extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($this->_params['productType']) || $this->_params['productType'] != Base::SORT_NEW_ONLINE) {
            return $productIds;
        }

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = strtotime($productsInfo[$id]['online_at']);
            $ids[] = $id;
        }

        array_multisort($sort, SORT_DESC,SORT_NUMERIC, $ids);

        return $ids;
    }
}
