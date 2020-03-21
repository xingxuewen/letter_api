<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Sort\SortAbstract;

/**
 * UV 点击数 降序
 *
 * Class UvPrice
 * @package App\Services\Lists\Sort\Items
 */
class UvClick extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        $ids = [];
        $sortUvPrice = [];
        $sortUvClick = [];
        foreach ($productIds as $id) {
            $sortUvPrice[] = $productsInfo[$id]['uv_price'];
            $sortUvClick[] = $productsInfo[$id]['uv_click'];
            $ids[] = $id;
        }

        array_multisort(
            $sortUvClick, SORT_DESC,SORT_NUMERIC,
            $sortUvPrice, SORT_DESC, SORT_NUMERIC,
            $ids
        );

        return $ids;
    }
}
