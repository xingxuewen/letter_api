<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\SubSet\SubSetAbstract;

/**
 * 优推荐计数
 *
 * Class Balance
 * @package App\Services\Lists\SubSet\Items
 */
class CountGood extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_CountGood';

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
        //var_dump('ab', Base::redis()->zrange($this->_cacheKey, 0, -1, ['WITHSCORES' => true]));exit;
        return Base::redis()->zrange($this->cacheKey(), 0, -1, true);
    }
}
