<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 我有 和 我要 (速贷大全列表)
 *
 * http://doc.sudaizhijia.com/index.php?s=/1&page_id=492
 * $this->_params['loanHas']
 * $this->_params['loanNeed']
 *
 * @package App\Services\Lists\Filter\Items
 */
class ParamsIHaveAndNeed extends FilterAbstract
{
    protected static $_cacheData = [];

    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = $this->_productIds;

        // 未设置
        if (empty($this->_params['loanHas']) && empty($this->_params['loanNeed'])) {
            return $ids;
        }

        $md5 = sprintf("%u", crc32($this->_params['loanHas'] . $this->_params['loanNeed']));

        if (isset(self::$_cacheData[$md5])) {
            return self::$_cacheData[$md5];
        }

        $loanHas = explode(',', $this->_params['loanHas']);
        $loanNeed = explode(',', $this->_params['loanNeed']);
        $tids = array_merge($loanHas, $loanNeed);
        $tids = array_map('intval', $tids);
        $tids = array_filter(array_unique($tids));

        if (empty($tids)) {
            return $ids;
        }

        $ids = Product::getProductByTags($ids, $tids, ProductConstant::PRODUCT_TAG_TYPE_LOAN_ID);

        self::$_cacheData[$md5] = $ids;

        return $ids;
    }
}
