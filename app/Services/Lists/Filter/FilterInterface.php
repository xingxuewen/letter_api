<?php
namespace App\Services\Lists\Filter;

interface FilterInterface
{
    /**
     * 过滤 $this->_productIds ，返回过滤后的 id 数组
     *
     * @return array
     *
     */
    public function filter() : array;
}