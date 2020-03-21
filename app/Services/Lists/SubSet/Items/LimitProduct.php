<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 当日总量已达限量产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class LimitProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_LimitProduct';

    public function setData($param = null)
    {
        //已到单日总限量
        $list = Product::getLimitProductIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
