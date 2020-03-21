<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Sort\SortAbstract;

/**
 * 上线时间 倒序排列
 *
 * Class UvPrice
 * @package App\Services\Lists\Sort\Items
 */
class Online extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = strtotime($productsInfo[$id]['online_at']);
            $ids[] = $id;
        }

        array_multisort(
            $sort, SORT_DESC,SORT_NUMERIC,
            $ids
        );

        return $ids;
    }
}
