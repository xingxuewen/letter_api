<?php
namespace App\Services\Lists\Sort;

interface SortInterface
{
    /**
     * 排序 $ids ，返回排序后的 id 数组
     *
     * @param array $productsInfo
     * @param array $productsIds
     * @return array
     *
     */
    public function sort(array $productsInfo, array $productsIds) : array;
}