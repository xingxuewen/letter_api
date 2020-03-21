<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;

/**
 * 行为产品
 *
 * @package App\Services\Lists\Filter\Items
 */
class ActionProduct extends FilterAbstract
{
    protected static $_downloadTime = null;

    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (!isset(self::$_downloadTime[$this->_user->id])) {
            self::$_downloadTime[$this->_user->id] = Product::productIsDownload($this->_user->id);
            //var_dump(self::$_downloadTime[$this->_user->id]);exit;
        }

        $downloadTime = self::$_downloadTime[$this->_user->id];

        // 下载时间24小时之后
        if ($downloadTime > 0 && (time() > $downloadTime + 86400)) {
            return $this->_productIds;
        }

        $ids = $this->_productIds;
        $_ids = Product::fetchNowBehaviorProduct($ids);

        if (!empty($_ids)) {
            $ids = array_diff($ids, $_ids);
        }

        return $ids;
    }
}
