<?php
namespace App\Services\Lists\SubSet\Items;

use App\Services\Lists\Base;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Sort\Items\Common;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceHotLoginOne;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceHotLoginThree;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceHotLoginTwo;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceHotNewUser;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceHotVip;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceLoginOne;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceLoginThree;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceLoginTwo;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceMainLoginOne;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceMainLoginThree;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceMainLoginTwo;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceMainNewUser;
use App\Services\Lists\SubSet\Items\BalanceItems\BalanceMainVip;
use App\Services\Lists\SubSet\SubSetAbstract;
use App\Services\Lists\User;

/**
 * 轮播
 *
 * Class Balance
 * @package App\Services\Lists\SubSet\Items
 */
class Balance extends SubSetAbstract
{
    protected $_cacheKey = 'lists_subset_Balance';

    protected $_rotateData = [];

    public function setData($param = null)
    {
        return $this->initRotate();
    }

    public function cacheKey()
    {
        return $this->_cacheKey . '_' . date('Ymd');
    }

    public function getData($ignoreEmpty = false)
    {
        $data = $this->getRotate($this->cacheKey());
        $data = $data ?: [];
        //print_r(self::IS_DEBUG);exit;
        if (empty($data) || $ignoreEmpty) {
            $data = $this->initRotate();
        }

        $this->updateRotate($this->cacheKey());
        //print_r($data);exit;

        logInfo('balance data', $data);

        return $data;

    }

    /**
     * 初始设置轮播列表
     *
     * @param $listKey
     * @param array $data
     */
    public function initRotate()
    {
        $data = (new NotMemberProduct())->getData();

        if (!empty($data)) {
            $data = (new Sort())->setProductIds($data)
                ->addSort(new Common())
                ->setParams($this->_params)
                ->sort();

            Base::redis()->ltrim($this->cacheKey(), 1, 0);
            Base::redis()->rpush($this->cacheKey(), ...$data);
            //print_r($this->getRotate($listKey));exit;
        }

        return $data;
    }

    /**
     * 获取轮播列表 redis list
     *
     * @param string $listKey
     * @return mixed
     */
    public function getRotate($listKey)
    {
        //var_dump($listKey);exit;
        $list = Base::redis()->lrange($listKey, 0, -1);

        //不为空取反
        if (!empty($list)) {
            krsort($list); // krsort 完键名不会改变，所以需要 array_values
            $list = array_values($list);
        }

        return $list ?: [];
    }

    /**
     * 更新轮播列表 redis list
     *
     * @param string $listKey
     * @return mixed
     */
    public function updateRotate($listKey)
    {
        return Base::redis()->rpoplpush($listKey, $listKey);
    }
}
