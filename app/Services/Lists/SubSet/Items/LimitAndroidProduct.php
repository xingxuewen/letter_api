<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * Android端已达限量产品 (subset是和用户无关的集合)
 *
 * @package App\Services\Lists\SubSet\Items
 */
class LimitAndroidProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_LimitAndroidProduct';

    public function setData($param = null)
    {
        //已到单日端总限量
        $list = Product::checkProductIsLimited(2);

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
