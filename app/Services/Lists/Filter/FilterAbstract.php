<?php
namespace App\Services\Lists\Filter;

use App\Services\Lists\Lists;

abstract class FilterAbstract implements FilterInterface
{
    use Lists;

    protected $_productInfo = [];

    public function setProductInfo($productInfo)
    {
        $this->_productInfo = $productInfo;
        return $this;
    }
}