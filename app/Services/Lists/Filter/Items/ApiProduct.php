<?php
namespace App\Services\Lists\Filter\Items;

use App\Constants\ProductConstant;
use App\Services\Lists\Filter\FilterAbstract;
use App\Services\Lists\InfoSet\Items\Product;
use Illuminate\Http\Request;

/**
 * 不支持api产品的端不展示api产品
 *
 * @package App\Services\Lists\Filter\Items
 */
class ApiProduct extends FilterAbstract
{
    const API_NEED_VERSION = '3.2.7';

    protected static $_appVersion = null;

    public function filter() : array
    {
        if (empty($this->_productIds)) {
            return [];
        }

        if (self::$_appVersion === null) {
            self::$_appVersion = app('request')->input('_app_version', '');
        }

        $ids = $this->_productIds;

        if (empty(self::$_appVersion)
            || version_compare(self::$_appVersion,self::API_NEED_VERSION, '<=')) {

            $apiProducts = [];
            foreach ($this->_productIds as $pid) {
                if (isset($this->_productInfo[$pid]) && $this->_productInfo[$pid]['is_api'] == '1') {
                    $apiProducts[] = $pid;
                }
            }
            $ids = array_diff($ids, $apiProducts);
        }

        return $ids;
    }
}
