<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 根据用户终端类型过滤产品
 * Class Area
 * @package App\Services\Lists\Filter\Items
 */
class UserTerminalType extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;
        //var_dump($this->_productIds, Product::get($ids));exit;
        if ($this->_user->terminalType > 0) {
            //var_dump($this->_user->terminalType, $ids);exit;
            //$info = Product::get($ids);
            $ids = Product::getProductByTerminal($this->_user->terminalType, $ids, true);
            //var_dump($ids, $info);exit;
            $ids = array_intersect($this->_productIds, $ids);
        }
        //var_dump($ids);exit;
        return $ids;
    }
}
