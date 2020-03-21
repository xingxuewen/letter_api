<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\InfoSet\Items\MemberInfo;
use App\Services\Lists\Sort\SortAbstract;
use App\Services\Lists\SubSet\Items\CountRecommend;

/**
 * 热门推荐第1位计数
 *
 * @package App\Services\Lists\Sort\Items
 */
class RecommendFirstCount extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        $counts = (new CountRecommend())->getData();

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
            $sortUv, SORT_DESC, SORT_NUMERIC,
            $sortCom, SORT_ASC, SORT_NUMERIC,
            $ids);

        return $ids;
    }
}
