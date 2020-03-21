<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\InfoSet\Items\MemberInfo;
use App\Services\Lists\Sort\SortAbstract;
use App\Services\Lists\SubSet\Items\CountGood;

/**
 * 优质推荐计数
 *
 * @package App\Services\Lists\Sort\Items
 */
class RecommendCount extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        // 优质推荐计数
        $counts = (new CountGood())->getData();

        $ids = [];
        $sort = [];
        $sortUv = [];
        $sortCom = [];
        foreach ($productIds as $id) {
            $sort[] = $counts[$id] ?? 0;
            $sortUv[] = $productsInfo[$id]['uv_price'];
            $sortCom[] = $productsInfo[$id]['position_sort'];
            $ids[] = $id;
        }

        array_multisort(
            $sort, SORT_ASC,SORT_NUMERIC,
            $sortUv, SORT_DESC,SORT_NUMERIC,
            $sortCom, SORT_ASC, SORT_NUMERIC,
            $ids
        );

        return $ids;
    }
}
