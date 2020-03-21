<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 不优质推荐产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class NotGoodRecommendProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_NotGoodRecommendProduct';

    public function setData($param = null)
    {
        $list = Product::getNotRecommendPatternProductIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
