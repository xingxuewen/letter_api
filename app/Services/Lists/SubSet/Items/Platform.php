<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 在线的平台id
 *
 * @package App\Services\Lists\SubSet\Items
 */
class Platform extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_Platform';

    public function setData($param = null)
    {
        $list = Product::fetchPlatformIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
