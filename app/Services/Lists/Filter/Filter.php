<?php
namespace App\Services\Lists\Filter;

use App\Services\Lists\Filter\Items\ActionProduct;
use App\Services\Lists\Filter\Items\ApiProduct;
use App\Services\Lists\Filter\Items\InShowTime;
use App\Services\Lists\Filter\Items\ParamsExcludeProduct;
use App\Services\Lists\Filter\Items\ParamsIHaveAndNeed;
use App\Services\Lists\Filter\Items\ParamsKeyword;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\ParamsQuota;
use App\Services\Lists\Filter\Items\ParamsTimeLimit;
use App\Services\Lists\Filter\Items\ParamsVipProduct;
use App\Services\Lists\Filter\Items\Platform;
use App\Services\Lists\Filter\Items\UserDelivery;
use App\Services\Lists\Filter\Items\UserDontWant;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\Filter\Items\UserArea;
use App\Services\Lists\Filter\Items\UserVip;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Lists;

class Filter
{
    use Lists;

    protected $_usePublicFilter = true;

    protected $_usePrivateFilter = true;

    protected $_useRequireFilter = true;

    protected $_productInfo = [];

    /**
     * 速贷大全输出前过滤器
     * (如果 ParamsKeyword 放在 requireFilter 过滤器，因为有位置要求，可能导致搜索不出来)
     *
     * @var array
     */
    public static $lastFilter = [
        ParamsKeyword::class,
        ParamsQuota::class,
        ParamsTimeLimit::class,
        ParamsIHaveAndNeed::class,
    ];

    public static $requireFilter = [
        // 不是vip用户务必不让看vip产品
        UserVip::class,
        Platform::class,
        //ApiProduct::class,

        // 根据条件过滤产品
        ParamsVipProduct::class,
        ParamsExcludeProduct::class,
    ];

    public static $publicItems = [
        InShowTime::class,
        MainPackage::class,
        ActionProduct::class,
    ];

    public static $privateItems = [
        UserArea::class,
        UserTerminalType::class,
        UserDelivery::class,
        UserDontWant::class,
    ];

    protected $_filter = [];

    protected $_validProductIds = [];

    public function usePublicFilter(bool $use)
    {
        $this->_usePublicFilter = $use;
        return $this;
    }

    public function usePrivateFilter(bool $use)
    {
        $this->_usePrivateFilter = $use;
        return $this;
    }

    public function useRequireFilter(bool $use)
    {
        $this->_useRequireFilter = $use;
        return $this;
    }

    public function addFilters(...$items)
    {
        foreach ($items as $item) {
            $this->addFilter($item);
        }

        return $this;
    }

    /**
     * @param FilterAbstract|string $items
     * @return $this
     */
    public function addFilter($items)
    {
        $this->_filter[] = $items;
        return $this;
    }

    public function filter() : self
    {
        // 初始化获取所有的产品信息
        $this->_productInfo = Product::get($this->_productIds);

        if (empty($this->_productInfo)) {
            return $this;
        }

        $productIds = array_column($this->_productInfo, 'platform_product_id');
        $pids = [];
        foreach ($this->_productIds as $pid) {
            if (in_array($pid, $productIds)) {
                $pids[] = $pid;
            }
        }
        if (empty($pids)) {
            return $this;
        }

        $this->_productIds = $pids;

        //var_dump($res, $this->_productIds);exit;

        //$publicIds = $this->_productIds;
        //$privateIds = $this->_productIds;

        logInfo('filter input', $this->_productIds);

        if ($this->_useRequireFilter) {
            $this->_productIds = $this->requireFilter();
            logInfo('filter require', $this->_productIds);
        }

        $this->_productIds = $this->otherFilter();
        logInfo('filter other', $this->_productIds);

        if ($this->_usePublicFilter) {
            $this->_productIds = $this->publicFilter();
            logInfo('filter public', $this->_productIds);
        }

        if ($this->_usePrivateFilter) {
            $this->_productIds = $this->privateFilter();
            logInfo('filter private', $this->_productIds);
        }

        //$validProductIds = array_intersect($publicIds, $privateIds, $other, $requireFilter);

        $this->setValidProductIds($this->_productIds);
        //var_dump($publicIds, $privateIds, $other, $this->_validProductIds);exit;
        return $this;
    }

    public function setValidProductIds(array $ids)
    {
        $this->_validProductIds = $ids;
        return $this;
    }

    public function getValidProductIds()
    {
        return $this->_validProductIds;
    }

    public static function getLastFilter()
    {
        return self::$lastFilter;
    }

    public function product($ids)
    {
        $ids = (array) $ids;

        if (empty($ids) || empty($this->_validProductIds)) {
            return [];
        }

        $res = [];

        foreach ($ids as $id) {
            if (in_array($id, $this->_validProductIds)) {
                $res[] = $id;
            }
        }

        return $res;
    }

    /**
     *
     * @return array 产品ID
     */
    public function privateFilter() : array
    {
        return $this->_filter(self::$privateItems);
    }

    /**
     *
     * @return array 产品ID
     */
    public function publicFilter() : array
    {
        return $this->_filter(self::$publicItems);
    }

    public function otherFilter()
    {
        return $this->_filter($this->_filter);
    }

    public function requireFilter()
    {
        return $this->_filter(self::$requireFilter);
    }

    /**
     *
     * @param array $items
     *
     * @return array 产品ID
     */
    protected function _filter(array $items) : array
    {
        $ids = $this->_productIds;

        //logInfo('filter input', ['ids' => $ids, 'params' => $this->_params, 'type' => $this->_type, 'user' => $this->_user]);

        if (!empty($ids) && !empty($items)) {
            $objects = [];

            foreach ($items as $class) {
                switch (true) {
                    case is_string($class):
                        try {
                            if (!class_exists($class)) {
                                continue;
                            }

                            $obj = new $class($this->_params);

                            if ($obj instanceof FilterAbstract) {
                                $objects[] = $obj;
                            }
                        } catch (\Exception $e) {
                            logError("{$class} filter exec error", $e->getTraceAsString());
                            continue;
                        }

                        break;

                    case $class instanceof FilterAbstract:
                        $objects[] = $class;
                        break;
                }
            }

            if (!empty($objects)) {
                foreach ($objects as $object) {
                    //logInfo('filter item input', ['class' => get_class($object), 'ids' => $ids]);
                    $ids = $object->setParams($this->_params)->setType($this->_type)->setUser($this->_user)->setProductIds($ids)->setProductInfo($this->_productInfo)->filter();
                    logInfo('filter item output', ['class' => get_class($object), 'ids' => $ids]);
                }
            }
        }

        return $ids;
    }
}