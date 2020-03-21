<?php
namespace App\Services\Lists\Logic;

use App\Helpers\RestResponseFactory;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\UserFactory;
use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Lists;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\LimitAndroidProduct;
use App\Services\Lists\SubSet\Items\LimitH5Product;
use App\Services\Lists\SubSet\Items\LimitIosProduct;
use App\Services\Lists\SubSet\Items\LimitProduct;
use App\Services\Lists\SubSet\Items\NewUserProduct;
use App\Services\Lists\SubSet\Items\PositionRequireProduct;

abstract class LogicAbstract implements LogicInterface
{
    use Lists;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    protected function _getLimitProducts()
    {
        return Product::limitProducts($this->_user->terminalType);
    }

    /**
     * 连登查看产品数计算
     */
    public function loginLookProductCount()
    {
        $total = ['newUser' => 10, 'login' => 0, 'vip' => 0];

        //用户登录信息
        $userLogin = UserFactory::fetchUserUnlockLoginTotalByUserId($this->_user->id);

        if (empty($userLogin)) {
            return $total;
        }

        //连登解锁规则
        $bannerUnlockLoginNewUser = BannersFactory::fetchBannerUnlockLoginNewUserByTypeId(1);

        if (empty($bannerUnlockLoginNewUser)) {
            return $total;
        }

        //用户最大连登天数
        $loginCount = $userLogin['login_count'];

        //新用户/连登1天老用户基础产品数
        $baseNum = $bannerUnlockLoginNewUser['unlock_pro_num'];

        //新用户/连登1天老用户每连登1天增加产品数
        $consecutiveNum = $bannerUnlockLoginNewUser['login_pro_num'];

        $total['newUser'] = $baseNum;
        $total['login'] = (max($loginCount, 1) - 1) * $consecutiveNum;

        logInfo('loginLookProductCount', $total);

        return $total;
    }

    /**
     * 满足位置要求的下一个位置 (0值)
     *
     * @param array $zeroArr  0值数组
     * @param bool $hasFirst  是否已有第一条数据
     * @param int $startPos   开始查找位置
     * @param int $needPos    要求的位置
     * @return int
     */
    public static function nextZeroPos(array $zeroArr, bool $hasFirst, int $startPos, int $needPos)
    {
        $len = count($zeroArr);

        if ($startPos >= $len) {
            return -1;
        }

        for ($i = $startPos + 1; $i < $len; $i++) {
            if ($zeroArr[$i] != 0) {
                continue;
            }

            // 不是在前3位，直接返回
            if ((!$hasFirst && $i > 2) || ($hasFirst && $i > 3)) {
                //var_dump('a', $i);exit;
                return $i;
            }

            // 有位置要求
            //$j = $hasFirst ? $i - 1 : $i;

            // 位置满足条件
            if ($needPos <= $i) {
                return $i;
            }

            //var_dump($j, $startPos, $needPos, $len);
        }
        //exit;
        return -1;
    }

    /**
     * 填充0值数组
     *
     * @param array $sourceProduct  数据源
     * @param array $zeroArr  0值数组
     * @param bool $hasFirst  是否已有第一条数据
     * @param array $positionRequireProduct 有位置要求的数据集合
     */
    public static function fillZeroArr(array $sourceProduct, array & $zeroArr,
                                       bool $hasFirst, array $positionRequireProduct)
    {
        if (empty($sourceProduct)) {
            return ;
        }

        foreach ($sourceProduct as $c) {
            $k = array_search(0, $zeroArr);

            // 如果位置满
            if ($k === false) {
                break;
            }

            // 前3已排满，无需判断位置，直接填数据
            if ((!$hasFirst && $k > 2) || ($hasFirst && $k > 3)) {
                $zeroArr[$k] = $c;
                continue;
            }

            //var_dump($k, $zeroArr);exit;

            // 没有位置要求，无需判断位置，直接填数据
            if (!isset($positionRequireProduct[$c])) {
                $zeroArr[$k] = $c;
                continue;
            }

            // 有位置要求
            $pos = (int) $positionRequireProduct[$c];
            //$j = $hasFirst ? $k - 1 : $k;

            // 位置满足条件
            if ($pos <= $k) {
                $zeroArr[$k] = $c;
                continue;
            }

            // 找下一个满足条件的空位置放数据
            $k = self::nextZeroPos($zeroArr, $hasFirst, $k, $pos);

            if ($k != -1) {
                $zeroArr[$k] = $c;
                continue;
            }
        }
    }

    /**
     * @param int $first
     * @param array $productC
     * @param array $productD
     * @param int $total
     * @param callable|null $callback
     * @return array
     */
    public function positionFillArr(int $first, array $productC, array $productD, int $total, callable $callback = null) : array
    {
        $e = array_fill(0, $total, 0);
        $positionRequireProduct = (new PositionRequireProduct())->getData();
        $positionRequireProductIds = empty($positionRequireProduct) ? [] : array_keys($positionRequireProduct);
        // TODO for test
        //$positionRequireProduct = [212 => 3];
        $hasFirst = $first > 0;

        if ($hasFirst > 0) {
            $e[0] = $first;
        }

        self::fillZeroArr($productC, $e, $hasFirst, $positionRequireProduct);
        logInfo('fillZeroArr 1', [$total, $e, $positionRequireProduct]);
        $k = array_search(0, $e);

        // 遍历完序列C所有元素后，已经没有空位置了，直接返回数据
        if ($k === false) {
            return $e;
        }

        if (!empty($productD)) {
            // 还有空位置，不断从序列D中取出uv单价最高的产品，填充序列E的空位置，直到序列E没有空位置或者序列D中所有产品都被取出；
            self::fillZeroArr($productD, $e, $hasFirst, $positionRequireProduct);
            logInfo('fillZeroArr 2', [$e, $positionRequireProduct]);
            $k = array_search(0, $e);

            // 遍历完序列D所有元素后，已经没有空位置了，直接返回数据
            if ($k === false) {
                return $e;
            }
        }

        $productAll = [];

        // 如果还有空位置，要死了......
        if ($callback !== null) {
            $productAll = $callback();
        }

        if (empty($productAll)) {
            return $e;
        }

        // 得到没有位置要求的产品
        $productAll = array_diff($productAll, $positionRequireProductIds);

        // 当前序列E中已确定位置的没有位置要求的产品
        $productE = array_diff($e, $positionRequireProductIds);

        // 过滤掉当前序列E中已确定位置的没有位置要求的产品
        $productAll = array_diff($productAll, $productE);

        // 过滤掉该设备终端不可见的产品，
        // 过滤掉主包/非主包不可见产品，得到集合B'
        $filter = (new Filter())->setUser($this->_user)
            ->usePublicFilter(false)
            ->usePrivateFilter(false)
            ->addFilters(new UserTerminalType(), new MainPackage())
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $productAll = $filter->getValidProductIds();

        // 对集合B中的产品按照uv单价降序排序、自动排序结果顺序，
        $productAll = (new Sort())->setProductIds($productAll)
            ->addSort(new UvPrice())
            ->sort();

        self::fillZeroArr($productAll, $e, $hasFirst, $positionRequireProduct);
        logInfo('fillZeroArr 3', [$e, $positionRequireProduct]);
        $e = array_filter($e);

        return $e;
    }
}