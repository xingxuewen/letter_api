<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Sort\SortAbstract;

/**
 * 综合排序
 *
 * Class Common
 * @package App\Services\Lists\Sort\Items
 */
class Common extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        //var_dump(array_keys($productsInfo), $productIds);exit;
        if (empty($this->_params['productType']) || $this->_params['productType'] == 1) {
            $ids = [];
            $sort = [];
            foreach ($productIds as $id) {
                $sort[] = $productsInfo[$id]['position_sort'];
                $ids[] = $id;
            }

            array_multisort($sort, SORT_ASC,SORT_NUMERIC, $ids);

            return $ids;
        }

        return $productIds;
    }
}
