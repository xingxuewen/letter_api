<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 所有有位置要求的产品
 *
 * @package App\Services\Lists\SubSet\Items
 * @return ["product_id int" => "position int"]
 */
class PositionRequireProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_PositionRequireProduct';

    public function setData($param = null)
    {
        $list = Product::getPositionProductIds();

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
