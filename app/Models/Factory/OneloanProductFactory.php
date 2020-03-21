<?php

namespace App\Models\Factory;

use App\Constants\ProductConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\SpreadProduct;

/**
 * 一键贷产品
 *
 * Class OneloanProductFactory
 * @package App\Models\Factory
 */
class OneloanProductFactory extends AbsModelFactory
{
    /**
     * 一键贷产品列表
     * @param array $data
     * @return array
     */
    public static function fetchSpreadProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $from = ProductConstant::PRODUCT_ONELOAN;
        //一键贷产品数据
        $query = SpreadProduct::select(['id', 'platform_product_id', 'h5_url', 'abut_switch'])
            ->where(['is_delete' => 0])
            ->whereIn('from', $from);

        //排序 按照position_sort进行排序 相同按照产品id排序
        $query->orderBy('position_sort', 'asc')->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }

        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */
        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;
        return $product ? $product : [];

    }

    /**
     * 一键贷产品列表信息
     *
     * @param $products
     * @return array
     */
    public static function fetchSpreadInfoProducts($products)
    {
        foreach ($products as $key => $val) {
            //单个产品信息
            $info = OneloanProductFactory::fetchProductOne($val['platform_product_id']);
            //单个产品标签
            $products[$key]['info'] = $info;
        }

        return $products ? $products : [];
    }

    /**
     * 一键贷使用单个产品信息
     *
     * @param $productId
     * @return array
     */
    public static function fetchProductOne($productId)
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid', 'p.is_vip_product'])
            ->where(['p.platform_product_id' => $productId])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->first();

        return $query ? $query->toArray() : [];
    }

    /**
     * 一键贷产品对接
     *
     * @param string $productId
     * @return array
     */
    public static function fetchWebsiteUrl($productId = '')
    {
        $product = SpreadProduct::from('sd_spread_product as sp')
            ->select(['sp.id', 'sp.h5_url', 'sp.abut_switch', 'p.type_nid', 'p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.type_nid', 'p.product_h5_url', 'p.official_website as product_official_website'])
            ->join('sd_platform_product as p', 'p.platform_product_id', '=', 'sp.platform_product_id')
            ->where(['sp.is_delete' => 0, 'sp.id' => $productId])
            ->first();

        return $product ? $product->toArray() : [];
    }

    /**
     * 一键贷获取产品信息
     *
     * @param $productId
     * @return array
     */
    public static function fetchProductname($productId)
    {
        $productArr = PlatformProduct::select(['platform_product_id', 'platform_product_name', 'platform_id', 'position_sort'])
            ->where(['platform_product_id' => $productId])
            ->first();
        return $productArr ? $productArr : [];
    }
}