<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * H5端已达限量产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class LimitH5Product extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_LimitH5Product';

    public function setData($param = null)
    {
        //已到单日端总限量
        $list = Product::checkProductIsLimited(3);

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
