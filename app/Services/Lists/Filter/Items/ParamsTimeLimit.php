<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 期限过滤 (速贷大全列表)
 *
 * http://doc.sudaizhijia.com/index.php?s=/1&page_id=492
 * $this->_params['loanTerm']
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsTimeLimit extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        // 未设置期限过滤
        if (empty($this->_params['loanTerm'])) {
            return $ids;
        }

        $loanTerm = explode(',', $this->_params['loanTerm']);
        $loanTerm = array_map('intval', $loanTerm);
        $loanTerm = array_filter($loanTerm);
        $min = $max = 0;

        if (count($loanTerm) == 1) {
            $min = $loanTerm[0];
            $max = 1000000000;
        } else {
            $min = $loanTerm[0];
            $max = $loanTerm[1];
        }

        $products = Product::get($ids);

        if (empty($products)) {
            return [];
        }

        $_ids = [];
        foreach ($ids as $id) {
            $product = $products[$id] ?? [];
            if (empty($product)) {
                continue;
            }

            if ($product['period_min'] > $max || $product['period_max'] < $min) {
                continue;
            }

            $_ids[] = $id;
        }

        return $_ids;
    }
}
