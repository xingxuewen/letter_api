<?php
namespace App\Services\Lists\SubSet;

use App\Services\Lists\SubSet\Items\Balance;
use App\Services\Lists\SubSet\Items\LimitAndroidProduct;
use App\Services\Lists\SubSet\Items\LimitH5Product;
use App\Services\Lists\SubSet\Items\LimitIosProduct;
use App\Services\Lists\SubSet\Items\LimitProduct;
use App\Services\Lists\SubSet\Items\MemberProduct;
use App\Services\Lists\SubSet\Items\NewUserProduct;

class SubSetListener
{
    protected $_items = [
        LimitProduct::class,
        LimitH5Product::class,
        LimitAndroidProduct::class,
        LimitIosProduct::class,
        Balance::class,
        MemberProduct::class,
        NewUserProduct::class,
    ];

    public function fire($callback)
    {
        if (!empty($this->_items)) {
            foreach ($this->_items as $item) {
                if (!class_exists($item)) {
                    continue;
                }

                $obj = new $item();

                if ($obj instanceof SubSetAbstract) {
                    //$callback($item);
                    call_user_func($callback, $obj);
                }
            }
        }
    }
}