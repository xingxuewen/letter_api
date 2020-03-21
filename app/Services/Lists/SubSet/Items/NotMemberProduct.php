<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 非会员产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class NotMemberProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_NotMemberProduct';

    public function setData($param = null)
    {
        $list = Product::getNotVipProductIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
