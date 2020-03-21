<?php
namespace App\Services\Lists\Logic\Items;


use App\Services\Lists\Sort\Items\Online;
use App\Services\Lists\Sort\Sort;

class MainNew extends MainClick
{
    protected function _sort($products)
    {
        return (new Sort())->setProductIds($products)
            ->addSort(new Online())
            ->sort();
    }
}