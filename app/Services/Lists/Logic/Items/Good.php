<?php
namespace App\Services\Lists\Logic\Items;

use App\Services\Lists\Filter\Filter;
use App\Services\Lists\Filter\Items\MainPackage;
use App\Services\Lists\Filter\Items\UserTerminalType;
use App\Services\Lists\InfoSet\Items\Product;
use App\Services\Lists\Sort\Items\RecommendCount;
use App\Services\Lists\Sort\Items\UvPrice;
use App\Services\Lists\Sort\Sort;
use App\Services\Lists\SubSet\Items\CountGood;
use App\Services\Lists\SubSet\Items\GoodProduct;
use App\Services\Lists\SubSet\Items\GoodProductType;
use App\Services\Lists\Logic\LogicAbstract;
use App\Services\Lists\SubSet\Items\NotMemberProduct;
use App\Services\Lists\SubSet\Items\NotGoodRecommendProduct;

class Good extends LogicAbstract
{
    const TYPE_CONFIG  = 0;
    const TYPE_AUTO    = 1;
    const TYPE_BALANCE = 2;

    public function getData() : array
    {
        $goodProductType = (new GoodProductType())->getData();
        $products = 0;
        //$goodProductType = 2;
        // 读取后台优质贷款配置项“推荐方式”，
        // 如果配置为“使用后台配置产品”执行步骤1.1，
        // 如果配置为“系统轮播选择产品”则执行步骤1.2，
        // 如果配置为“系统自动选择产品”则执行步骤1.3；
        switch ($goodProductType) {
            case self::TYPE_CONFIG:
                $products = $this->_config();
                break;

            case self::TYPE_AUTO:
                $products = $this->_auto();
                break;

            case self::TYPE_BALANCE:
                $products = $this->_balance();
                break;
        }

        $products = intval($products);

        return $products > 0 ? [$products] : [];
    }

    protected function _config()
    {
        // 读取后台配置项“优质产品推荐”，
        $goodProduct = (new GoodProduct())->getData();
        //var_dump('abc', $goodProduct);exit;
        // 如果该配置项没有值，就自动选择优质贷款推荐，执行步骤1.3;
        if (empty($goodProduct)) {
            return $this->_auto();
        }

        // 在线的非会员产品集合A；
        $notMember = (new NotMemberProduct())->getData();

        // 没有值,执行步骤1.3;
        if (empty($notMember)) {
            return $this->_auto();
        }

        // 根据用户ID确定该用户可见的在线的非会员产品集合A
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($notMember)
            ->setParams($this->_params)
            ->filter();

        $notMember = $filter->getValidProductIds();

        //没有值,执行步骤1.3;
        if (empty($notMember)) {
            return $this->_auto();
        }

        // 如果后台配置的产品属于集合A，即产品为该用户可见的在线的非会员产品，则返回该配置产品作为优质贷款推荐的产品；
        if (in_array($goodProduct[0], $notMember)) {
            return $goodProduct[0];
        }

        // 如果配置的产品不属于集合A，就自动选择优质贷款推荐，执行步骤1.3；
        return $this->_auto();
    }

    protected function _auto()
    {
        // 在线的非会员产品集合A
        $notMemberProduct = (new NotMemberProduct())->getData();

        // 读取后台配置的推荐模块不展示的产品集合B
        $notRecommendProduct = (new NotGoodRecommendProduct())->getData();

        $limitProduct = Product::limitProducts($this->_user->terminalType);

        // 从集合A中过滤掉集合B中的产品，得到集合C
        $notMemberProduct = array_diff($notMemberProduct, $limitProduct);
        $productsAll = array_diff($notMemberProduct, $notRecommendProduct);

        // 根据用户ID确定该用户可见的产品集合
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($productsAll)
            ->setParams($this->_params)
            ->filter();

        //过滤后的产品集合C
        $products = $filter->getValidProductIds();

        if (!empty($products)) {
            //uv单价降
            $products = (new Sort())->setProductIds($products)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();

            return $products[0];
        }

        // 如果集合C为空集
        // 取所有在线的非会员产品，过滤掉该设备终端不可见的产品，过滤掉主包/非主包不可见的产品，剩余的产品集合为D
        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($notMemberProduct)
            ->setParams($this->_params)
            ->usePrivateFilter(false)
            ->usePublicFilter(false)
            ->addFilters(new UserTerminalType(), new MainPackage())
            ->filter();

        $productsAll = $filter->getValidProductIds();

        // 若剩余的产品集合D为空，则客户端新增空白页，页面展示：小诸葛+提示文案：很抱歉，暂无推荐产品，请稍后再试~
        if (empty($productsAll)) {
            return [];
        }

        // 若剩余的产品集合D不为空，从集合D中去掉后台配置的该模块不展示的产品集合，得到产品集合E
        $products = array_diff($productsAll, $notRecommendProduct);

        // 若集合E为空，则取集合D中UV单价最高的产品，作为优质贷款推荐的产品;
        if (empty($products)) {
            //uv单价降
            $products = (new Sort())->setProductIds($productsAll)
                ->addSort(new UvPrice())
                ->setParams($this->_params)
                ->sort();
            return $products[0];
        }

        // 若集合E不为空，取集合E中UV单价最高的产品，作为优质贷款推荐的产品;
        $products = (new Sort())->setProductIds($products)
            ->addSort(new UvPrice())
            ->setParams($this->_params)
            ->sort();

        return $products[0];
    }

    protected function _balance()
    {
        // 取所有在线的非会员产品集合E
        $notMemberProduct = (new NotMemberProduct())->getData();

        // 读取后台配置的推荐模块不展示的产品集合B
        $notRecommendProduct = (new NotGoodRecommendProduct())->getData();

        $limitProduct = Product::limitProducts($this->_user->terminalType);

        // 集合E中过滤掉集合B中存在的产品，得到轮播集合C；
        $products = array_diff($notMemberProduct, $notRecommendProduct, $limitProduct);

        // 没有值,执行步骤1.3;
        if (empty($products)) {
            return $this->_auto();
        }

        // 将集合C中的产品按照优质贷款推荐计数升序，UV单价降序排列，得到序列H；
        $products = (new Sort())->setProductIds($products)
            ->addSorts(new RecommendCount())
            ->setParams($this->_params)
            ->sort();

        $filter = (new Filter())->setUser($this->_user)
            ->setProductIds($notMemberProduct)
            ->setParams($this->_params)
            ->filter();

        // 根据用户ID确定该用户可见的在线的非会员产品集合A；
        $notMemberProduct = $filter->getValidProductIds();

        // 从序列H中顺序查找，
        $products = array_intersect($products, $notMemberProduct);

        // 若遍历序列H中的所有产品，未能找到属于集合A的产品，则：执行步骤1.3；
        if (empty($products)) {
            return $this->_auto();
        }

        // 若找到一个产品a，产品a属于集合A；则：产品a的优质贷款推荐计数增加1，产品a作为本次请求的优质贷款推荐产品；
        // 推荐计数加 1
        $pid = current($products);
        (new CountGood())->setData(['id' => $pid]);

        return intval($pid);
    }
}