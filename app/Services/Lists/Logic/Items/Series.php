<?php
namespace App\Services\Lists\Logic\Items;

use App\Services\Lists\Base;
use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\Sort\Items\RecommendCount;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\CountGood;
use App\Services\Lists\SubSet\Items\GoodProduct;
use App\Services\Lists\SubSet\Items\GoodProductType;
use App\Services\Lists\Logic\LogicAbstract;
use App\Services\Lists\SubSet\Items\NotMemberProduct;
use App\Services\Lists\SubSet\Items\NotRecommendProduct;

class Series extends LogicAbstract
{
    public function getData() : array
    {
        $products = [];

        switch ($this->_type) {
            case Base::TYPE_SERIES_THREE:
                $products = $this->_one();
                break;

            case Base::TYPE_SERIES_TWO:
                $products = $this->_two();
                break;

            case Base::TYPE_SERIES_ONE:
            default:
                $products = $this->_three();
                break;
        }

        return $products ?: [];
    }

    protected function _one()
    {

    }

    protected function _two()
    {

    }

    protected function _three()
    {

    }
}