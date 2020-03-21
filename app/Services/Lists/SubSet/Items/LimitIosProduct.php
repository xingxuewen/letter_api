<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * ios端已达限量产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class LimitIosProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_LimitIosProduct';

    public function setData($param = null)
    {
        //已到单日端总限量
        $list = Product::checkProductIsLimited(1);

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
