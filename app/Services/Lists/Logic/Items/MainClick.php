<?php
namespace App\Services\Lists\Logic\Items;

use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\Logic\LogicAbstract;
use App\Services\Lists\Sort\Items\UvClick;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\MemberProduct;
use App\Services\Lists\SubSet\Items\NewUserProduct;
use App\Services\Lists\SubSet\Items\NotMemberProduct;

class MainClick extends LogicAbstract
{
    public function getData() : array
    {
        $totalArr = $this->loginLookProductCount();
        $products = [];

        switch (true) {
            case $this->_user->isVip == 1:
                $products = $this->_vip($totalArr);
                break;

            case $this->_user->loginType > 1:
                $products = $this->_login($totalArr);
                break;

            case $this->_user->loginType <= 1:
            default:
                $products = $this->_newUser($totalArr);
        }

        // 排序
        return $this->_sort($products);
    }

    /**
     * @param $totalArr
     * @return array
     */
    protected function _newUser($totalArr)
    {
        $total = $totalArr['newUser'];

        // 在线的新用户产品集合
        $newUserProductAll = (new NewUserProduct())->getData();

        // 已达限量但未下架
        $limitAll = $this->_getLimitProducts();

        // 已达限量但未下架的新用户产品集合
        $limitAll = array_intersect($limitAll, $newUserProductAll);

        // 未达限量的在线的新用户产品集合
        $newUserProductAll = array_diff($newUserProductAll, $limitAll);

        $productAll = array_merge($newUserProductAll, $limitAll);

        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $newUserProduct = $filter->product($newUserProductAll);
        $limit = $filter->product($limitAll);

        $newUserProduct = (new Sort())->setProductIds($newUserProduct)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $limit = (new Sort())->setProductIds($limit)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        $productAll = array_merge($newUserProduct, $limit);

        if (empty($productAll)) {
            $filter = (new Filter())->setUser($this->_user)
                ->usePublicFilter(false)
                ->usePrivateFilter(false)
                ->addFilters(new UserTerminalType(), new MainPackage())
                ->setProductIds(array_merge($newUserProductAll, $limitAll))
                ->setParams($this->_params)
                ->filter();

            $newUserProduct = $filter->product($newUserProductAll);
            $limit = $filter->product($limitAll);

            $newUserProduct = (new Sort())->setProductIds($newUserProduct)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $limit = (new Sort())->setProductIds($limit)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $productAll = array_merge($newUserProduct, $limit);
        }

        $products = array_slice($productAll, 0, $total);

        return $products;
    }

    protected function _login($totalArr)
    {
        $total = $totalArr['login'];
        $newUser = $this->_newUser($totalArr);
        $notMemberProductAll = (new NotMemberProduct())->getData();
        $limitAll = $this->_getLimitProducts();
        $limitAll = array_intersect($limitAll, $notMemberProductAll);
        $notMemberProductAll = array_diff($notMemberProductAll, $limitAll);

        // 分别过滤掉第1步得出的新用户可见产品集合M
        $notMemberProductAll = array_diff($notMemberProductAll, $newUser);
        $limitAll = array_diff($limitAll, $newUser);

        // 按照在线产品在前，限量产品在后排列得到序列F
        $productAll = array_merge($notMemberProductAll, $limitAll);

        // 根据该请求的设备终端、地域、渠道、不想看产品、行为产品规则、时段显示规则、主包是否可见规则
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
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

        $productAll = array_merge($notMemberProduct, $limit);

        if (empty($productAll)) {
            // 过滤掉该设备终端不可见的产品，
            // 过滤掉主包/非主包不可见产品
            $filter = (new Filter())->setUser($this->_user)
                ->usePublicFilter(false)
                ->usePrivateFilter(false)
                ->addFilters(new UserTerminalType(), new MainPackage())
                ->setProductIds(array_merge($notMemberProductAll, $limitAll))
                ->setParams($this->_params)
                ->filter();

            $notMemberProductAll = $filter->product($notMemberProductAll);
            $limitAll = $filter->product($limitAll);

            if (empty($notMemberProductAll) && empty($limitAll)) {
                return [];
            }

            $notMemberProduct = (new Sort())->setProductIds($notMemberProductAll)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $limit = (new Sort())->setProductIds($limitAll)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            $productAll = array_merge($notMemberProduct, $limit);
        }

        $productAll = array_slice($productAll, 0, $total);

        // 则该请求对应的可见产品集合R=第1步得出的产品集合M和第4步得出的产品集合S的并集
        $products = array_merge($newUser, $productAll);

        return $products;
    }

    protected function _vip($totalArr)
    {
        $notMemberProductAll = (new NotMemberProduct())->getData();
        $memberProductAll = (new MemberProduct())->getData();

        $productAll = array_unique(array_merge($notMemberProductAll, $memberProductAll));

        // 过滤产品
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productAll)
            ->setParams($this->_params)
            ->filter();

        $notMemberProduct = $filter->product($notMemberProductAll);
        $memberProduct = $filter->product($memberProductAll);

        $products = array_merge($notMemberProduct, $memberProduct);

        return $products;
    }

    protected function _sort($products)
    {
        return (new Sort())->setProductIds($products)
            ->addSort(new UvClick())
            ->setParams($this->_params)
            ->sort();
    }
}