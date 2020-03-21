<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\SubSet\Items\MemberProduct;

/**
 * 非会员用户不能查看会员产品
 * Class Area
 * @package App\Services\Lists\Filter\Items
 */
class UserVip extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        // 不是会员，过滤掉会员产品
        if ($this->_user->isVip == 0) {
            $_ids = (new MemberProduct())->getData();
            $ids = array_diff($ids, $_ids);
        }

        return $ids;
    }
}
