<?php
namespace App\Services\Lists\Sort;

use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Lists;
use App\Services\Lists\Sort\Items\Common;
use App\Services\Lists\Sort\Items\ParamsInterestRate;
use App\Services\Lists\Sort\Items\ParamsNewOnline;
use App\Services\Lists\Sort\Items\ParamsQuota;
use App\Services\Lists\Sort\Items\ParamsSpeed;
use App\Services\Lists\Sort\Items\ParamsSuccessRate;

class Sort
{
    use Lists;

    protected $_sort = [];

    protected $_commonSort = [
        Common::class,
        //HasApply::class,
    ];

    public function addSorts(...$sorts)
    {
        foreach ($sorts as $sort) {
            $this->addSort($sort);
        }
        return $this;
    }

    public function addSort(SortAbstract $sort)
    {
        $this->_sort[] = $sort;
        return $this;
    }

    protected function _commonSort()
    {
        $this->_sort($this->_commonSort);
    }

    public function sort()
    {
        return $this->_sort($this->_sort);
    }

    protected function _sort(array $sorts)
    {
        //logInfo('sort input', ['ids' => $this->_productIds, 'params' => $this->_params, 'type' => $this->_type, 'user' => $this->_user]);

        //var_dump($this->_sort);exit;
        if (empty($this->_productIds)) {
            return [];
        }

        if (empty($sorts)) {
            return $this->_productIds;
        }

        $products = Product::get($this->_productIds);

        if (empty($products)) {
            return [];
        }

        $productIds = array_column($products, 'platform_product_id');
        $pids = [];
        foreach ($this->_productIds as $pid) {
            if (in_array($pid, $productIds)) {
                $pids[] = $pid;
            }
        }
        if (empty($pids)) {
            return [];
        }

        $this->_productIds = $pids;

        if (!empty($this->_productIds)) {
            $objects = [];

            foreach ($sorts as $class) {
                switch (true) {
                    case is_string($class):
                        try {
                            if (!class_exists($class)) {
                                continue;
                            }

                            $obj = new $class($this->_params);
                            if ($obj instanceof SortAbstract) {
                                $objects[] = $obj;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }

                        break;

                    case $class instanceof SortAbstract:
                        $objects[] = $class;
                        break;
                }
            }

            if (!empty($objects)) {
                foreach ($objects as $object) {
                    logInfo('sort item input', ['class' => get_class($object), 'ids' => $this->_productIds]);

                    $object->setParams($this->_params);
                    if (!empty($this->_user)) {
                        $object->setUser($this->_user);
                    }
                    $this->_productIds = $object->sort($products, $this->_productIds);

                    logInfo('sort item output', ['class' => get_class($object), 'ids' => $this->_productIds]);
                }
            }
        }

        return $this->_productIds;
    }
}