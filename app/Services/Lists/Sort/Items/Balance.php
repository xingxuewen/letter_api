<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\Sort\SortAbstract;

/**
 * 轮播排序
 *
 * Class Balance
 * @package App\Services\Lists\Sort\Items
 */
class Balance extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        $balance = new \App\Services\Lists\SubSet\Items\Balance();
        $balanceData = $balance->getData();

        if (empty($balanceData)) {
            return $productIds;
        }

        $ids = [];
        $sort = [];
        $total = count($balanceData);
        foreach ($productIds as $id) {
            $index = array_search($id, $balanceData);
            if ($index !== false) {
                $sort[] = $index;
            } else {
                $sort[] = $total++;
            }
            $ids[] = $id;
        }

        array_multisort($sort, SORT_ASC,SORT_NUMERIC, $ids);

        //print_r($ids);print_r($balanceData);exit;

        return $ids;
    }
}
