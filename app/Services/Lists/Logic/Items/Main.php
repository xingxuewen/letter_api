<?php
namespace App\Services\Lists\Logic\Items;

use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Logic\LogicAbstract;
use App\Services\Lists\Sort\Items\Balance;
use App\Services\Lists\Sort\Items\Common;
use App\Services\Lists\Sort\Items\MainFirstCount;
use App\Services\Lists\Sort\Items\ParamsInterestRate;
use App\Services\Lists\Sort\Items\ParamsNewOnline;
use App\Services\Lists\Sort\Items\ParamsQuota;
use App\Services\Lists\Sort\Items\ParamsSpeed;
use App\Services\Lists\Sort\Items\ParamsSuccessRate;
use App\Services\Lists\Sort\Items\Position;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\CountMain;
use App\Services\Lists\SubSet\Items\CountRecommend;
use App\Services\Lists\SubSet\Items\LimitAndroidProduct;
use App\Services\Lists\SubSet\Items\LimitH5Product;
use App\Services\Lists\SubSet\Items\LimitIosProduct;
use App\Services\Lists\SubSet\Items\LimitProduct;
use App\Services\Lists\SubSet\Items\MemberProduct;
use App\Services\Lists\SubSet\Items\NewUserProduct;
use App\Services\Lists\SubSet\Items\NotMemberProduct;
use App\Services\Lists\SubSet\Items\PositionRequireProduct;

class Main extends LogicAbstract
{
    protected $_inBalance = false;

    protected $_sortType = 1;

    public function getData() : array
    {
        $this->_inBalance = Product::checkIfCirculateDatetime() == 1;
        $this->_sortType = $this->_params['productType'] ?? 1;

        // 是否轮播时间 & 综合排序
        if (0 && $this->_inBalance && $this->_sortType == 1) {
            logInfo('in balance');
            $products = $this->_balance();
        } else {
            $totalArr = $this->loginLookProductCount();
            logInfo('not in balance', ['totalArr' => $totalArr]);
            $first = $this->_first();
            logInfo("first data {$first}");
            $products = $this->_default($first, $totalArr);
            $products = $this->_lastFilter($products);
            $products = $this->_lastSort($products);
        }

        logInfo('logic get data', $products);

        return $products;
    }

    protected function _lastFilter($products)
    {
        $lastFilter = Filter::getLastFilter();
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($products)
            ->setParams($this->_params)
            ->usePrivateFilter(false)
            ->usePublicFilter(false)
            ->useRequireFilter(false)
            ->addFilters(...$lastFilter)
            ->filter();

        return $filter->getValidProductIds();
    }

    protected function _lastSort($products)
    {
        if ($this->_sortType == 1) {
            return $products;
        }

        $sort = (new Sort())->setProductIds($products)
            ->setUser($this->_user)
            ->setParams($this->_params)
            ->addSorts(new ParamsInterestRate, new ParamsNewOnline,
                new ParamsQuota, new ParamsSpeed, new ParamsSuccessRate);

        return $sort->sort();
    }

    protected function _balance()
    {
        $totalArr = $this->loginLookProductCount();

        switch (true) {
            case $this->_user->isVip == 1:
                return $this->_balanceVip($totalArr);
                break;

            case $this->_user->loginType > 1:
                return $this->_balanceLogin($totalArr);
                break;

            case $this->_user->loginType <= 1:
            default:
                return $this->_balanceNewUser($totalArr);
        }
    }

    protected function _balanceNewUser($totalArr)
    {
        $total = $totalArr['newUser'];
        $newUserProductAll = (new NewUserProduct())->getData();
        $limitAll = $this->_getLimitProducts();
        $limitAll = array_intersect($limitAll, $newUserProductAll);
        $newUserProductAll = array_diff($newUserProductAll, $limitAll);

        $productAll = array_merge($newUserProductAll, $limitAll);

        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $newUserProduct = $filter->product($newUserProductAll);
        $limit = $filter->product($limitAll);

        $newUserProduct = (new Sort())->setProductIds($newUserProduct)
            ->setUser($this->_user)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $limit = (new Sort())->setProductIds($limit)
            ->setUser($this->_user)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $productAll = array_merge($newUserProduct, $limit);

        if (empty($productAll)) {
            $newUserProduct = (new Sort())->setProductIds($newUserProductAll)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $limit = (new Sort())->setProductIds($limitAll)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $productAll = array_merge($newUserProduct, $limit);
        }

        $products = array_slice($productAll, 0, $total);

        $products = (new Sort())->setProductIds($products)
            ->setUser($this->_user)
            ->addSorts(new Balance())
            ->setParams($this->_params)
            ->sort();

        return $products;
    }

    protected function _balanceLogin($totalArr)
    {
        $loginTotal = $totalArr['login'];
        $newUser = $this->_balanceNewUser($totalArr);
        $notMemberProductAll = (new NotMemberProduct())->getData();
        $limitAll = $this->_getLimitProducts();
        $limitAll = array_intersect($limitAll, $notMemberProductAll);
        $notMemberProductAll = array_diff($notMemberProductAll, $limitAll);

        $notMemberProductAll = array_diff($notMemberProductAll, $newUser);
        $limitAll = array_diff($limitAll, $newUser);

        $productAll = array_merge($notMemberProductAll, $limitAll);

        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $notMemberProduct = $filter->product($notMemberProductAll);
        $limit = $filter->product($limitAll);

        $notMemberProduct = (new Sort())->setProductIds($notMemberProduct)
            ->setUser($this->_user)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $limit = (new Sort())->setProductIds($limit)
            ->setUser($this->_user)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $productAll = array_merge($notMemberProduct, $limit);

        if (empty($productAll)) {
            $notMemberProduct = (new Sort())->setProductIds($notMemberProductAll)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $limit = (new Sort())->setProductIds($limitAll)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $productAll = array_merge($notMemberProduct, $limit);
        }

        $productAll = array_slice($productAll, 0, $loginTotal);
        $products = array_merge($newUser, $productAll);

        $products = (new Sort())->setProductIds($products)
            ->setUser($this->_user)
            ->addSort(new Balance())
            ->setParams($this->_params)
            ->sort();

        return $products;
    }

    protected function _balanceVip($totalArr)
    {
        $notMemberProduct = (new NotMemberProduct())->getData();
        $memberProduct = (new MemberProduct())->getData();

        $productAll = array_merge($notMemberProduct, $memberProduct);

        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $notMemberProduct = $filter->product($notMemberProduct);
        $memberProduct = $filter->product($memberProduct);

        $notMemberProduct = (new Sort())->setProductIds($notMemberProduct)
            ->addSort(new Balance())
            ->setParams($this->_params)
            ->sort();

        $memberProduct = (new Sort())->setProductIds($memberProduct)
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        $products = array_merge($notMemberProduct, $memberProduct);

        return $products;
    }

    /**
     * @param $first
     * @param $totalArr
     * @return array
     */
    protected function _default($first, $totalArr)
    {
        switch (true) {
        case $this->_user->isVip == 1:
            return $this->_vip($first, $totalArr);
            break;

        case $this->_user->loginType > 1:
            if ($totalArr['newUser'] == 0 && $totalArr['login'] == 0) {
                return [];
            }
            return $this->_login($first, $totalArr);
            break;

        case $this->_user->loginType <= 1:
        default:
            if ($totalArr['newUser'] == 0) {
                return [];
            }
            return $this->_newUser($first, $totalArr);
        }
    }

    protected function _vip($first, $total)
    {
        $firstArr = $first > 0 ? [$first] : [];
        $notMemberProductAll = (new NotMemberProduct())->getData();
        $memberProductAll = (new MemberProduct())->getData();
        $productAll = array_diff(array_unique(array_merge($notMemberProductAll, $memberProductAll)), $firstArr);
        $notMemberProductAll = array_diff($notMemberProductAll, $firstArr);
        $memberProductAll = array_diff($memberProductAll, $firstArr);

        // 过滤产品
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $notMemberProduct = $filter->product($notMemberProductAll);
        $memberProduct = $filter->product($memberProductAll);

        $notMemberProduct = (new Sort())->setProductIds($notMemberProduct)
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        $count = count($notMemberProduct) + ($first > 0 ? 1 : 0);
        $notMemberProduct = $this->positionFillArr($first, $notMemberProduct, [], $count, function() use ($firstArr) {
            $notMemberProductAll = (new NotMemberProduct())->getData();
            $memberProductAll = (new MemberProduct())->getData();
            $productAll = array_unique(array_merge($notMemberProductAll, $memberProductAll));
            return array_diff($productAll, $firstArr);
        });

        $memberProduct = (new Sort())->setProductIds($memberProduct)
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        $products = array_merge(array_diff($notMemberProduct, $memberProduct), $memberProduct);

        return $products;
    }

    protected function _login($first, $totalArr)
    {
        //$firstArr = $first > 0 ? [$first] : [];
        $loginTotal = $totalArr['login'];
        //$newUserTotal = $totalArr['newUser'];
        $newUser = $this->_newUser($first, $totalArr);
        $notMemberProductAll = (new NotMemberProduct())->getData();
        $limitAll = $this->_getLimitProducts();

        $limitAll = array_diff(array_intersect($limitAll, $notMemberProductAll), $newUser);
        $notMemberProductAll = array_diff($notMemberProductAll, $limitAll, $newUser);
        $productsAll = array_merge($notMemberProductAll, $limitAll);

        // 过滤产品
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productsAll)
            ->setParams($this->_params)
            ->filter();

        $notMemberProduct = $filter->product($notMemberProductAll);
        $limit = $filter->product($limitAll);

        if (!empty($notMemberProduct)) {
            $notMemberProduct = (new Sort())->setProductIds($notMemberProduct)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();
        }

        if (!empty($limit)) {
            $limit = (new Sort())->setProductIds($limit)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();
        }

        $products = array_merge($notMemberProduct, $limit);

        logInfo('main product list 1', [
            'newUser' => $newUser,
            'notMemberProduct' => $notMemberProduct,
            'limit' => $limit,
            'products' => $products,
            'loginTotal' => $loginTotal,
        ]);

        if (empty($products)) {
            // 过滤掉该设备终端不可见的产品，
            // 过滤掉主包/非主包不可见产品，得到集合B'
            $filter = (new Filter())->setUser($this->_user)
                ->usePublicFilter(false)
                ->usePrivateFilter(false)
                ->addFilters(new UserTerminalType(), new MainPackage())
                ->setProductIds($productsAll)
                ->setParams($this->_params)
                ->filter();

            $notMemberProduct = $filter->product($notMemberProductAll);
            $limit = $filter->product($limitAll);

            $notMemberProduct = (new Sort())->setProductIds($notMemberProduct)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $limit = (new Sort())->setProductIds($limit)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $products = array_merge($notMemberProduct, $limit);

            logInfo('main product list 2', [
                'notMemberProduct' => $notMemberProduct,
                'limit' => $limit,
                'products' => $products,
            ]);
        }

        $productsC = array_slice($products, 0, $loginTotal);
        $productsD = array_slice($products, $loginTotal);

        // 将第2步得到的序列E去掉排在第1位的产品，
        $newUserTotal = count($newUser);
        if (!empty($newUser)) {
            $first = array_shift($newUser);
        }

        logInfo('product c d', [$productsC, $productsD]);

        // 剩余的产品和第4.3步得到的序列G中的产品按照自动排序的结果顺序排列得到序列H
        $productsAll = (new Sort())->setProductIds(array_merge($productsC, $newUser))
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        $products = $this->positionFillArr($first, $productsAll, $productsD, $newUserTotal + count($productsC),
            function() {
                return (new NotMemberProduct())->getData();
            }
        );

        return $products;
    }

    protected function _newUser(int $first, $totalArr)
    {
        $firstArr = $first > 0 ? [$first] : [];
        $total = empty($firstArr) ? $totalArr['newUser'] : $totalArr['newUser'] - 1;
        $newUserProductAll = (new NewUserProduct())->getData();

        // 过滤产品
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($newUserProductAll)
            ->setParams($this->_params)
            ->filter();

        $newUserProductValidAll = $filter->getValidProductIds();
        $limit = $this->_getLimitProducts();
        $newUserProduct = array_diff($newUserProductValidAll, $limit, $firstArr);
        $limit = array_diff(array_intersect($limit, $newUserProductValidAll), $firstArr);

        if (!empty($newUserProduct)) {
            $newUserProduct = (new Sort())->setProductIds($newUserProduct)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();
        }

        if (!empty($limit)) {
            $limit = (new Sort())->setProductIds($limit)
                ->setUser($this->_user)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();
        }

        $products = array_merge($newUserProduct, $limit);

        logInfo('main newUser list', [
            $newUserProduct, $limit, $products, $total,
        ]);

        if (empty($products)) {
            $newUserProduct = array_diff($newUserProductAll, $firstArr);
            $limit = array_diff(array_intersect($limit, $newUserProductAll), $firstArr);

            // 过滤掉该设备终端不可见的产品，
            // 过滤掉主包/非主包不可见产品，得到集合B'
            $filter = (new Filter())->setUser($this->_user)
                ->usePublicFilter(false)
                ->usePrivateFilter(false)
                ->addFilters(new UserTerminalType(), new MainPackage())
                ->setProductIds($newUserProduct)
                ->setParams($this->_params)
                ->filter();

            $newUserProduct = $filter->product($newUserProduct);
            $limit = $filter->product($limit);

            if (!empty($newUserProduct)) {
                $newUserProduct = (new Sort())->setProductIds($newUserProduct)
                    ->setUser($this->_user)
                    ->addSort(new UvPrice())
                    ->setParams($this->_params)
                    ->sort();
            }

            if (!empty($limit)) {
                $limit = (new Sort())->setProductIds($limit)
                    ->setUser($this->_user)
                    ->addSort(new UvPrice())
                    ->setParams($this->_params)
                    ->sort();
            }

            $products = array_merge($newUserProduct, $limit);
        }

        $productsC = array_slice($products, 0, $total);
        $productsD = array_slice($products, $total);

        // TODO for test
        //$first = 0;
        //$productsC = [1, 212, 256, 297, 45, 294, 98];
        //$productsD = [];
        //$total = 10;
        //var_dump('ad');exit;

        // 选位置之前自动排序
        $productsC = (new Sort())->setProductIds($productsC)
            ->addSort(new Common())
            ->setParams($this->_params)
            ->sort();

        if (count($productsC) < $total) {
            $total = count($productsC);
        }

        $total+= $first > 0 ? 1 : 0;

        $products = $this->positionFillArr($first, $productsC, $productsD, $total, function () use ($firstArr) {
            // 读取后台配置的当前在线的新用户产品集合
            $product = (new NewUserProduct())->getData();
            return array_diff($product, $firstArr);
        });

        return $products;
    }

    protected function _first() : int
    {
        // 获取数贷大全第1位是否轮播
        $isBalance = (int) Product::getIsCarousel(2);

        if (!$isBalance) {
            return 0;
        }

        $newUserProduct = (new NewUserProduct())->getData();
        $positionRequireProduct = (new PositionRequireProduct())->getData();
        $positionRequireProductIds = empty($positionRequireProduct) ? [] : array_keys($positionRequireProduct);

        $products = array_diff($newUserProduct, $positionRequireProductIds);

        if (empty($products)) {
            return 0;
        }

        // 过滤产品
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($products)
            ->setParams($this->_params)
            ->filter();

        $products = $filter->getValidProductIds();

        if (empty($products)) {
            return 0;
        }

        $products = (new Sort())->setProductIds($products)
            ->setUser($this->_user)
            ->addSort(new MainFirstCount())
            ->setParams($this->_params)
            ->sort();

        // 推荐计数加 1
        (new CountMain())->setData(['id' => $products[0]]);

        return intval($products[0]);


    }
}