<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Models\ComModelFactory;
use App\Models\Factory\ProductFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * Class CreditcardTypeStrategy
 * @package App\Strategies
 * 信用卡类型策略层
 */
class CreditcardTypeStrategy extends AppStrategy
{

    /**
     * @param $params
     * @param $datas
     * @return mixed
     * 信用卡用途类型
     */
    public static function getUsageType($params, $datas)
    {
        foreach ($datas as $key => $val) {
            foreach ($params as $k => $v) {
                $params[$key + 1]['id'] = $val['id'];
                $params[$key + 1]['name'] = $val['name'];
                $params[$key + 1]['type_nid'] = $val['type_nid'];
            }
        }

        return $params;
    }

    /**
     * 第二版 分类专题产品数据个数处理
     * @param array $params
     * @return mixed
     */
    public static function getSpecialLists($params = [])
    {
        //某分类专题详情
        $specialIds = $params['specialIds'];
        $specialLists = isset($params['specialLists']) ? $params['specialLists'] : [];
        $productType = $params['productType'];

        $result = [];
        $productIdArr = explode(',', $specialIds['product_list']);
        foreach ($productIdArr as $key => $value) {
            foreach ($specialLists as $k => $val) {
                if ($value == $val['platform_product_id']) {
                    $result[$key] = $val;
                    $result[$key]['productType'] = $productType;
                }
            }
        }

        //处理图片
        $product = [];
        foreach ($result as $rkey => $rval) {
            $product[$rkey]['platform_product_id'] = $rval['platform_product_id'];
            $product[$rkey]['platform_id'] = $rval['platform_id'];
            $product[$rkey]['platform_product_name'] = $rval['platform_product_name'];
            $product[$rkey]['product_logo'] = QiniuService::getProductImgs($rval['product_logo'], $rval['platform_product_id']);
            //标签
            $tag = ProductFactory::fetchTagByProId($rval['platform_product_id']);
            $product[$rkey]['tag_name'] = isset($tag['tag_name']) ? $tag['tag_name'] : '';
            $product[$rkey]['is_tag'] = isset($tag['is_tag']) ? intval($tag['is_tag']) : 0;
            $successCount = bcadd($rval['success_count'], 0);
            $todayTotalCount = bcadd($rval['total_today_count'], 0);
            $product[$rkey]['success_count'] = DateUtils::ceilMoney($todayTotalCount);
            //今日申请总数
            $product[$rkey]['total_today_count'] = DateUtils::ceilMoney($todayTotalCount);
            $product[$rkey]['product_introduct'] = ComModelFactory::escapeHtml($rval['product_introduct']);
            //额度
            $product[$rkey]['interest_alg'] = $rval['interest_alg'];
            $loan_min = DateUtils::formatIntToThous($rval['loan_min']);
            $loan_max = DateUtils::formatIntToThous($rval['loan_max']);
            $product[$rkey]['quota'] = $loan_min . '~' . $loan_max;
            //期限
            $period_min = ProductStrategy::formatDayToMonthByInterestalg($rval['interest_alg'], $rval['period_min']);
            $period_max = ProductStrategy::formatDayToMonthByInterestalg($rval['interest_alg'], $rval['period_max']);
            $product[$rkey]['term'] = $period_min . '~' . $period_max;
            $product[$rkey]['productType'] = intval($productType);
            //日、月利息
            $product[$rkey]['interest_rate'] = $rval['min_rate'] . '%';
            //下款时间
            $loanSpeed = empty($rval['value']) ? '3600' : $rval['value'];
            $product[$rkey]['loan_speed'] = ProductStrategy::formatLoanSpeed($loanSpeed) . '小时';
            //是否是速贷优选产品
            $product[$rkey]['is_preference'] = isset($rval['is_preference']) ? $rval['is_preference'] : 0;
            //加密手机号
            $product[$rkey]['mobile'] = ProductStrategy::fetchEncryptMobile($params);
            //对接标识
            $product[$rkey]['type_nid'] = $rval['type_nid'] ? strtolower($rval['type_nid']) : '';
        }
        $result = array_values($product);
        $datas['list'] = $result ? $result : RestUtils::getStdObj();
        $datas['pageCount'] = $params['pageCount'];
        $datas['title'] = isset($specialIds['title']) ? $specialIds['title'] : '';
        $datas['id'] = isset($specialIds['id']) ? $specialIds['id'] : '';
        $datas['remark'] = isset($specialIds['remark']) ? $specialIds['remark'] : '';
        $datas['bg_color'] = isset($specialIds['bg_color']) ? $specialIds['bg_color'] : '';
        $datas['img'] = isset($specialIds['img']) ? QiniuService::getImgs($specialIds['img']) : '';
        return $datas;
    }
}