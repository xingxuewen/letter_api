<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\MemberInfo;

/**
 * 已申请过的产品 热门推荐 不显示
 *
 * @package App\Services\Lists\Sort\Items
 */
class UserHasApply extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        // 热门推荐不显示已申请的产品
        if (!empty($this->_user->id)) {
            $pids = MemberInfo::fetchUserClickedProductIds($this->_user->id);
            $ids = empty($pids) ? $ids : array_diff($ids, $pids);
        }

        return $ids;
    }
}
