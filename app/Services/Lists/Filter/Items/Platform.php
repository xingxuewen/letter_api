<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 产品对应的平台是否已下线
 *
 * @package App\Services\Lists\Filter\Items
 */
class Platform extends FilterAbstract
{
    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        $ids = [];

        $platformIds = (new \App\Services\Lists\SubSet\Items\Platform())->getData();

        if (empty($platformIds)) {
            return [];
        }

        foreach ($this->_productIds as $id) {
            if (in_array($this->_productInfo[$id]['platform_id'], $platformIds)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
