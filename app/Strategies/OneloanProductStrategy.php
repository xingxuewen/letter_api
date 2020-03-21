<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\Utils;
use App\Services\Core\OneloanApply\OneloanApplyService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 一键贷产品
 *
 * Class OneloanProductStrategy
 * @package App\Strategies
 */
class OneloanProductStrategy extends AppStrategy
{
    /**
     * 一键贷产品列表
     *
     * @param array $params
     * @return array
     */
    public static function getSpreadProducts($params = [])
    {
        $products = $params['list'];
        $mobile['mobile'] = $params['mobile'];

        $lists = [];
        foreach ($products as $key => $val) {
            $lists[$key]['id'] = $val['id'];
            $lists[$key]['platform_product_id'] = $val['platform_product_id'];
            $lists[$key]['platform_id'] = isset($val['info']) ? $val['info']['platform_id'] : 0;
            $lists[$key]['platform_product_name'] = isset($val['info']) ? $val['info']['platform_product_name'] : '';
            $lists[$key]['product_logo'] = isset($val['info']) ? QiniuService::getProductImgs($val['info']['product_logo'], $val['platform_product_id']) : '';
            $todayTotalCount = bcadd($val['info']['total_today_count'], 0);
            $lists[$key]['success_count'] = isset($val['info']) ? $val['info']['success_count'] : 0;
            $lists[$key]['total_today_count'] = isset($val['info']) ? DateUtils::ceilMoney($todayTotalCount) : '';
            $lists[$key]['product_introduct'] = isset($val['info']) ? Utils::removeHTML($val['info']['product_introduct']) : '';
            $lists[$key]['interest_alg'] = isset($val['info']) ? $val['info']['interest_alg'] : '';
            $loan_min = isset($val['info']) ? DateUtils::formatIntToThous($val['info']['loan_min']) : '';
            $loan_max = isset($val['info']) ? DateUtils::formatIntToThous($val['info']['loan_max']) : '';
            $lists[$key]['quota'] = $loan_min . '~' . $loan_max;
            //期限
            $period_min = isset($val['info']) ? ProductStrategy::formatDayToMonthByInterestalg($val['info']['interest_alg'], $val['info']['period_min']) : '';
            $period_max = isset($val['info']) ? ProductStrategy::formatDayToMonthByInterestalg($val['info']['interest_alg'], $val['info']['period_max']) : '';
            $lists[$key]['term'] = $period_min . '~' . $period_max;

            //日、月利息
            $lists[$key]['interest_rate'] = isset($val['info']) ? $val['info']['min_rate'] . '%' : '';
            //下款时间
            $loanSpeed = isset($val['info']) ? (empty($val['info']['value']) ? '3600' : $val['info']['value']) : '';
            $lists[$key]['loan_speed'] = ProductStrategy::formatLoanSpeed($loanSpeed) . '小时';

            $lists[$key]['mobile'] = ProductStrategy::fetchEncryptMobile($mobile);
            $lists[$key]['type_nid'] = isset($val['type_nid']) ? $val['info']['type_nid'] : '';
            $lists[$key]['productType'] = 1;
        }

        return $lists ? $lists : [];
    }

    /**
     * 一键贷产品列表 - 立即申请
     *
     * @param array $datas
     * @return mixed
     */
    public static function getWebsite($datas = [])
    {
        //平台数据
        //$params = $datas['platform'];
        //产品数据
        $params = $datas['product'];

        $datas['page'] = !empty($params['h5_register_link']) ? $params['h5_register_link'] : $params['official_website'];
        //调取service
        $page['url'] = OneloanApplyService::i()->toOneloanApplyService($datas);

        return $page;
    }

    /**
     * @param $mobile
     * @param $params
     * @return array
     * 产品/平台数据处理
     */
    public static function getOauthProductDatas($data, $user, $params)
    {
        $data['user']['username'] = isset($user['user']['username']) ? $user['user']['username'] : '';
        $data['user']['mobile'] = isset($user['user']['mobile']) ? $user['user']['mobile'] : '';
        $data['user']['sex'] = isset($user['profile']['sex']) ? $user['profile']['sex'] : '';
        $data['user']['real_name'] = isset($user['profile']['real_name']) ? $user['profile']['real_name'] : '';
        $data['user']['idcard'] = isset($user['profile']['identity_card']) ? $user['profile']['identity_card'] : '';
        //产品数据
        $data['product']['id'] = $params['id'];
        $data['product']['h5_register_link'] = $params['h5_url'];
        $data['product']['register_link'] = $params['product_h5_url'];
        $data['product']['official_website'] = $params['product_official_website'];
        $data['product']['type_nid'] = $params['type_nid'];
        $data['product']['channel_status'] = $params['abut_switch'];
        $data['product']['type_nid'] = $params['type_nid'];
        $data['product']['product_name'] = $params['platform_product_name'];
        $data['product']['product_id'] = $params['platform_product_id'];
        $data['product']['platform_id'] = $params['platform_id'];

        return $data ? $data : [];

    }
}