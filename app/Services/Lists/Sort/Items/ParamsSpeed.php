<?php
namespace App\Services\Lists\Sort\Items;

use App\Constants\ProductConstant;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\PlatformProductProperty;
use App\Services\Lists\Base;
use App\Services\Lists\Sort\SortAbstract;

/**
 * 放款速度
 *
 * @package App\Services\Lists\Sort\Items
 */
class ParamsSpeed extends SortAbstract
{
    public function sort(array $productsInfo, array $productIds) : array
    {
        if (empty($this->_params['productType']) || $this->_params['productType'] != Base::SORT_SPEED) {
            return $productIds;
        }

        $res = PlatformProductProperty::select(['product_id', 'key'])
            ->where(['key' => ProductConstant::PRODUCT_LOAN_TIME])
            ->whereIn('product_id', $productIds)
            ->get()
            ->toArray();

        if (empty($res)) {
            return $productIds;
        }

        $loan = array_column($res, 'key', 'product_id');

        $ids = [];
        $sort = [];
        foreach ($productIds as $id) {
            $sort[] = $loan[$id] ?? 0;
            $ids[] = $id;
        }

        array_multisort($sort, SORT_DESC,SORT_NUMERIC, $ids);

        return $ids;
    }
}
