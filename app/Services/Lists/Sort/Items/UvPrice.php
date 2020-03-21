<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Sort\SortAbstract;

/**
 * UV 单价
 *
 * Class UvPrice
 * @package App\Services\Lists\Sort\Items
 */
class UvPrice extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        $ids = [];
        $sortUv = [];
        $sortCom = [];
        foreach ($productIds as $id) {
            $sortUv[] = $productsInfo[$id]['uv_price'];
            $sortCom[] = $productsInfo[$id]['position_sort'];
            $ids[] = $id;
        }

        array_multisort(
            $sortUv, SORT_DESC,SORT_NUMERIC,
            $sortCom, SORT_ASC, SORT_NUMERIC,
            $ids
        );

        return $ids;
    }
}
