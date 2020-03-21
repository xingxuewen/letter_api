<?php
namespace App\Services\Lists\Logic\Items;

use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserDontWant;
use App\Services\Lists\Filter\Items\UserHasApply;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Sort\Items\Balance;
use App\Services\Lists\Sort\Items\Common;
use App\Services\Lists\Sort\Items\RecommendFirstCount;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\CountRecommend;
use App\Services\Lists\SubSet\Items\GoodProduct;
use App\Services\Lists\SubSet\Items\NewUserProduct;
use App\Services\Lists\Logic\LogicAbstract;
use App\Services\Lists\SubSet\Items\NotRecommendProduct;
use App\Services\Lists\SubSet\Items\PositionRequireProduct;

class Hot extends LogicAbstract
{
    public function getData() : array
    {
        // 是否轮播时间 （暂时不开启轮播）
        //var_dump(Product::checkIfCirculateDatetime());exit;
        if (0 && Product::checkIfCirculateDatetime()) {
            $products = $this->_balance();
        } else {
            // 热门推荐展示产品数量  $total
            $total = (int) Product::getProductRecommendShowNum();

            if ($total <= 0) {
                return [];
            }

            // 选择第1位的产品
            $first = $this->_first();
            $products = $this->_other($first, $total);
        }

        return $products;
    }

    protected function _balance()
    {
        $newUserProduct = (new NewUserProduct())->getData();
        $notRecommendProduct = (new NotRecommendProduct())->getData();
        $goodProduct = (new GoodProduct())->getData();

        $productsAll = array_diff($newUserProduct, $notRecommendProduct, $goodProduct);
        $products = [];

        if (empty($productsAll)) {
            $products = $newUserProduct;
        } else {
            // 过滤产品
            $filter = (new Filter())->setUser($this->_user)
                ->addFilters(new UserHasApply())
                ->setParams($this->_params)
                ->setProductIds($productsAll)
                ->filter();

            $products = $filter->getValidProductIds();

            if (empty($products)) {
                $products = $productsAll;
            }
        }

        $products = (new Sort())->setProductIds($products)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        // 获取热门推荐展示产品数量
        $total = (int) Product::getProductRecommendShowNum();
        $products = array_slice($products, 0, $total);

        $products = (new Sort())->setProductIds($products)
            ->addSort(new Balance())
            ->setParams($this->_params)
            ->sort();

        return $products;
    }

    /**
     * 热门推荐第1位的产品
     *
     * @return int
     */
    protected function _first() : int
    {
        // 读取后台热门推荐模块配置项“热门推荐第1位产品是否轮播”的值，
        $isBalance = (int) Product::getIsCarousel(1);

        // 若为是，则执行以下全部流程；若为否，则不选取第1位产品，直接从步骤2开始执行；
        if (!$isBalance) {
            return 0;
        }

        // 读取后台配置的当前在线的新用户产品集合
        $newUserProduct = (new NewUserProduct())->getData();

        // 优质贷款推荐模块推荐的产品
        $goodProduct = (new GoodProduct())->getData();

        // 所有有位置要求的产品
        $positionRequireProduct = (new PositionRequireProduct())->getData();
        $positionRequireProductIds = empty($positionRequireProduct) ? [] : array_keys($positionRequireProduct);

        // 热门推荐不展示产品
        $notRecommendProduct = (new NotRecommendProduct())->getData();

        $limitProduct = Product::limitProducts($this->_user->terminalType);

        // 过滤掉当前优质贷款推荐模块推荐的产品，过滤掉后台配置的所有有位置要求的产品，过滤掉后台配置的热门推荐不展示产品
        $products = array_diff($newUserProduct, $goodProduct, $positionRequireProductIds, $notRecommendProduct, $limitProduct);

        if (empty($products)) {
            return 0;
        }

        // 根据该请求的设备终端、地域、渠道、不想看产品、行为产品规则、时段显示规则、主包是否可见规则、
        // 热门推荐点击过的产品不展示规则，过滤掉该请求不可见的在线产品，得到第1位产品的选取集合A
        $filter = (new Filter())->setUser($this->_user)
            ->addFilters(new UserHasApply())
            ->setProductIds($products)
            ->setParams($this->_params)
            ->filter();

        //得到第1位产品的选取集合A
        $products = $filter->getValidProductIds();

        if (empty($products)) {
            return 0;
        }

        $products = (new Sort())->setProductIds($products)
            ->addSort(new RecommendFirstCount())
            ->setParams($this->_params)
            ->sort();

        // 推荐计数加 1
        (new CountRecommend())->setData(['id' => $products[0]]);

        return intval($products[0]);
    }

    /**
     * 其他热门推荐产品
     *
     * @param $first
     * @return array
     */
    protected function _other($first, $total)
    {
        $total = $total <= 0 ? 10 : $total;
        $total = $first > 0 ? $total - 1 : $total;

        // 第1位的产品
        $firstArr = $first > 0 ? [$first] : [];

        // 读取后台配置的当前在线的新用户产品集合
        $newUserProduct = (new NewUserProduct())->getData();

        // 当前优质贷款推荐模块推荐的产品集合
        $goodProduct = (new GoodProduct())->getData();

        // 后台配置的热门推荐不展示产品
        $notRecommendProduct = (new NotRecommendProduct())->getData();

        $limitProduct = Product::limitProducts($this->_user->terminalType);

        // 过滤掉第1步中选出的第1位的产品，过滤掉当前优质贷款推荐模块推荐的产品，过滤掉后台配置的热门推荐不展示产品
        $products = array_diff($newUserProduct, $firstArr, $goodProduct, $notRecommendProduct, $limitProduct);

        logInfo('hot other list', [
            'newUserProduct' => $newUserProduct,
            'firstArr' => $firstArr,
            'goodProduct' => $goodProduct,
            'notRecommendProduct' => $notRecommendProduct,
            'products' => $products,
            'limitProduct' => $limitProduct,
        ]);

        // 根据该请求的设备终端、地域、渠道、不想看产品、行为产品规则、时段显示规则、主包是否可见规则、
        // 热门推荐点击过的产品不展示规则，过滤掉该请求不可见的在线产品，得到其他热门推荐产品的选取集合B；
        $filter = (new Filter())->setUser($this->_user)
            ->addFilters(new UserHasApply())
            ->setProductIds($products)
            ->setParams($this->_params)
            ->filter();

        // 得到其他热门推荐产品的选取集合B
        $products = $filter->getValidProductIds();

        logInfo('hot other list products', $products);

        if (empty($products)) {
            // 若集合B为空集，取当前在线的新用户产品，过滤掉第1步中选出的第1位产品，
            // 过滤掉当前优质贷款推荐模块推荐的产品，
            $products = array_diff($newUserProduct, $firstArr, $goodProduct);

            // 为空返回
            if (empty($products)) {
                return [];
            }

            // 过滤掉该设备终端不可见的产品，
            // 过滤掉主包/非主包不可见产品，得到集合B'
            $filter = (new Filter())->setUser($this->_user)
                ->usePublicFilter(false)
                ->usePrivateFilter(false)
                ->addFilters(new UserTerminalType(), new MainPackage())
                ->setProductIds($products)
                ->setParams($this->_params)
                ->filter();

            $products = $filter->getValidProductIds();
            $products = array_diff($products, $limitProduct);

            // 为空返回
            if (empty($products)) {
                return [];
            }
        }

        // 对集合B中的产品按照uv单价降序排序、自动排序结果顺序，
        $products = (new Sort())->setProductIds($products)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        // 取前m个作为序列C，剩余部分作为序列D；
        $productsC = array_slice($products, 0, $total);
        $productsD = array_slice($products, $total);

        // 将序列C按照自动排序的结果顺序排列，得到序列c1， c2，c3，...，cm；
        $productsC = (new Sort())->setProductIds($productsC)
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        $total = count($productsC) + ($first > 0 ? 1 : 0);

        $products = $this->positionFillArr($first, $productsC, $productsD, $total, function () {
            // 读取后台配置的当前在线的新用户产品集合
            $newUserProduct = (new NewUserProduct())->getData();
            $limitProduct = Product::limitProducts($this->_user->terminalType);
            return array_diff($newUserProduct, $limitProduct);
        });

        return $products;
    }
}