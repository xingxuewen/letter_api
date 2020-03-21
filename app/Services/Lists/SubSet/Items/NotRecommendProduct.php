<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 推荐模块不展示产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class NotRecommendProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_NotRecommendProduct';

    public function setData($param = null)
    {
        $list = Product::getNotRecommendProductIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
