<?php
namespace App\Services\Lists\Sort\Items;

use App\Services\Lists\InfoSet\Items\MemberInfo;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 已申请过的产品 排在最底下
 *
 * @package App\Services\Lists\Sort\Items
 */
class HasApply extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($productIds)) {
            return [];
        }

        if (empty($this->_user->id)) {
            return $productIds;
        }

        $ids = MemberInfo::fetchUserClickedProductIds($this->_user->id);

        if (empty($ids)) {
            return $productIds;
        }

        $ids = array_intersect($productIds, $ids);

        if (empty($ids)) {
            return $productIds;
        }

        $pids = $iids = [];

        foreach ($productIds as $id) {
            if (in_array($id, $ids)) {
                $iids[] = $id;
            } else {
                $pids[] = $id;
            }
        }

        return array_merge($pids, $iids);
    }
}
