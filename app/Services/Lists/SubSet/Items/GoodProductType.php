<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 优质推荐产品方式
 *
 * @package App\Services\Lists\SubSet\Items
 */
class GoodProductType extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_GoodProductType';

    public function setData($param = null)
    {
        $list = Product::getRecommendPattern();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
