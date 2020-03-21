<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 关键词搜索
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsKeyword extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (empty($this->_params['keyword'])) {
            return $this->_productIds;
        }

        $productList = Product::get($this->_productIds);

        if (empty($productList)) {
            return [];
        }

        $ids = [];

        foreach ($productList as $product) {
            if (mb_strpos($product['platform_product_name'], $this->_params['keyword']) !== false) {
                $ids[] = $product['platform_product_id'];
            }
        }

        return $ids;
    }
}
