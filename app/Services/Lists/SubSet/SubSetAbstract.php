<?php
namespace App\Services\Lists\SubSet;

use App\Services\Lists\Base;
use App\Services\Lists\Lists;

abstract class SubSetAbstract implements SubSetInterface
{
    use Lists;

    const IS_DEBUG = true;

    protected $_cacheKey = '';

    protected $_cacheExpire = 1800;

    private static $_cacheData = [];

    protected $_ignoreEmpty = false;

    public function setIgnoreEmpty(bool $val)
    {
        $this->_ignoreEmpty = $val;
        return $this;
    }

    public function getData()
    {
        if (empty($this->_cacheKey)) {
            return [];
        }

        if (isset(self::$_cacheData[$this->_cacheKey])) {
            return self::$_cacheData[$this->_cacheKey];
        }

        $data = Base::redis()->get($this->_cacheKey);

        // 不存在
        if ($data === false) {
            $data = $this->setData();
        } else {
            $data = @json_decode($data, true);
        }

        self::$_cacheData[$this->_cacheKey] = $data;

        logInfo('subset get data', ['class' => get_class($this), 'data' => $data]);

        return $data;
    }

    public function setData($param = null)
    {

    }

    /**
     * 产品更新回调函数
     *
     * @param int|array $productId
     */
    public function productUpdateListener($productId)
    {
        $productId = (array) $productId;
        $ids = $this->getData();

        if (!empty($ids) && !empty($productId) && array_intersect($ids, $productId)) {
            $this->setData();
        }
    }
}