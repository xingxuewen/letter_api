<?php
namespace App\Services\Lists\Filter\Items;

use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 额度过滤 (速贷大全列表)
 *
 * http://doc.sudaizhijia.com/index.php?s=/1&page_id=492
 * $this->_params['loanAmount']
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsQuota extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        // 未设置额度过滤
        if (empty($this->_params['loanAmount'])) {
            return $ids;
        }

        $loanAmount = trim($this->_params['loanAmount'], ',');
        $loanAmount = explode(',', $loanAmount);
        $loanAmount = array_map('intval', $loanAmount);
        //$loanAmount = array_filter($loanAmount);
        $min = $max = 0;

        if (count($loanAmount) == 1) {
            $min = $loanAmount[0];
            $max = 900000000;
        } else {
            $min = $loanAmount[0];
            $max = $loanAmount[1];
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

            if ($product['loan_min'] > $max || $product['loan_max'] < $min) {
                continue;
            }
            $_ids[] = $id;
        }

        return $_ids;
    }
}
