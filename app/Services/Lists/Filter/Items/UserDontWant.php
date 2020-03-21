<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\MemberInfo;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\Items\MemberProduct;

/**
 * 用户不想看的产品
 * Class Area
 * @package App\Services\Lists\Filter\Items
 */
class UserDontWant extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (empty($this->_user->id)) {
            return $this->_productIds;
        }

        $ids = $this->_productIds;
        $product = MemberInfo::getUserProductBlack($this->_user->id);

        //去掉用户不想看的产品
        if (!empty($product)) {
            $ids = array_diff($ids, $product);
        }

        return $ids;
    }
}
