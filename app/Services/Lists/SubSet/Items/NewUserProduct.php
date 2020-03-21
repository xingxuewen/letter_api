<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 新用户解锁产品
 *
 * @package App\Services\Lists\SubSet\Items
 */
class NewUserProduct extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_NewUserProduct';

    public function setData($param = null)
    {
        $list = Product::getUnlockLoginProducts(Product::UNLOCK_LOGIN_TYPE_NEW_USER);

        Base::redis()->setex($this->_cacheKey, $this->_cacheExpire, json_encode($list));

        return $list;
    }

}
