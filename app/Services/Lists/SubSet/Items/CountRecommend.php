<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 热门推荐第一位计数
 *
 * Class Balance
 * @package App\Services\Lists\SubSet\Items
 */
class CountRecommend extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_CountRecommend';

    public function cacheKey()
    {
        return $this->_cacheKey . '_' . date('Ymd');
    }

    public function setData($product = null)
    {
        $productId = empty($product['id']) ? 0 : intval($product['id']);
        $score = empty($product['score']) ? 1 : intval($product['score']);

        if ($productId > 0) {
            Base::redis()->zincrby($this->cacheKey(), $score, $productId);
        }
    }

    public function getData()
    {
        return Base::redis()->zrange($this->cacheKey(), 0, -1, true);
    }
}
