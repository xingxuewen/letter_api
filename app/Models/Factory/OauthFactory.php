<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\CooperateProduct;
use App\Models\Orm\DataProductAccessLog;
use App\Models\Orm\DataProductEncryptAccessLog;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\SpreadProduct;
use App\Models\Orm\UserAuth;
use App\Services\Core\Platform\PlatformService;

class OauthFactory extends AbsModelFactory
{
    //判断平台开关
    public static function checkChannelStatus($id, $channelStatus)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status'])
            ->addSelect(['pf.channel_status'])
            ->where(['pf.channel_status' => $channelStatus])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $id
     * @param $channelStatus
     * @return array
     * 判断产品开关
     */
    public static function checkProductChannelStatus($id, $channelStatus)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status'])
            ->addSelect(['pf.channel_status'])
            ->where(['p.channel_status' => $channelStatus])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $id
     * @param $channelStatus
     * @return array
     * 判断产品开关
     */
    public static function checkProductChannelStatusNothing($id, $channelStatus)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status'])
            ->addSelect(['pf.channel_status'])
            ->where(['p.channel_status' => $channelStatus])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * 一键选贷款 - 判断是否需要对接
     *
     * @param $id
     * @param $channelStatus
     * @return array
     */
    public static function checkOneloanChannelStatus($id, $channelStatus)
    {
        $product = SpreadProduct::from('sd_spread_product as sp')
            ->select(['sp.id', 'sp.h5_url', 'sp.abut_switch', 'p.type_nid', 'p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.type_nid'])
            ->join('sd_platform_product as p', 'p.platform_product_id', '=', 'sp.platform_product_id')
            ->where(['sp.is_delete' => 0, 'sp.id' => $id, 'sp.abut_switch' => $channelStatus])
            ->first();

        return $product ? $product->toArray() : [];
    }

    /**
     * 合作贷 - 判断是否需要对接
     *
     * @param $id
     * @param $channelStatus
     * @return array
     */
    public static function checkCoopeChannelStatus($id, $channelStatus)
    {
        $product = CooperateProduct::from('sd_cooperate_product as cp')
            ->select(['cp.id', 'cp.url as h5_url', 'cp.abut_switch', 'p.type_nid', 'p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.type_nid'])
            ->join('sd_platform_product as p', 'p.platform_product_id', '=', 'cp.product_id')
            ->where(['cp.is_delete' => 0, 'cp.id' => $id, 'cp.abut_switch' => $channelStatus])
            ->first();

        return $product ? $product->toArray() : [];
    }

    /**
     * 撞库开关
     *
     * @param $id
     * @param int $isButt
     * @return array
     */
    public static function checkProductIsButt($id, $isButt = 0)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status', 'p.is_butt'])
            ->addSelect(['pf.channel_status'])
            ->where(['p.is_butt' => $isButt])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * 撞库开关
     * 与产品上线线没有关系
     *
     * @param $id
     * @param int $isButt
     * @return array
     */
    public static function checkProductIsButtNothing($id, $isButt = 0)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status', 'p.is_butt'])
            ->addSelect(['pf.channel_status'])
            ->where(['p.is_butt' => $isButt])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $logData
     * 对接平台返回流水
     */
    public static function createDataProductAccessLog($datas = [])
    {
        $log = new DataProductAccessLog();
        $log->user_id = $datas['userId'];
        $log->username = $datas['username'];
        $log->mobile = $datas['mobile'];
        $log->platform_id = $datas['platformId'];
        $log->platform_product_id = $datas['productId'];
        $log->platform_product_name = $datas['product']['product_name'];
        $log->apply_url = $datas['apply_url'];
        $log->channel_no = $datas['channel_no'];
        $log->is_new_user = $datas['is_new_user'];
        $log->feedback_message = isset($datas['feedback_message']) ? $datas['feedback_message'] : '';
        $log->complete_degree = isset($datas['complete_degree']) ? $datas['complete_degree'] : '';
        $log->qualify_status = isset($datas['qualify_status']) ? $datas['qualify_status'] : 99;
        $log->period_type = isset($datas['period_type']) ? $datas['period_type'] : '0';
        $log->period = isset($datas['period']) ? $datas['period'] : '';
        $log->amount_min = isset($datas['amount_min']) ? $datas['amount_min'] : 0;
        $log->amount_max = isset($datas['amount_max']) ? $datas['amount_max'] : 0;
        $log->success_rate = isset($datas['success_rate']) ? $datas['success_rate'] : 0;
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->create_at = date('Y-m-d H:i:s', time());
        $log->create_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * 加密撞库流水
     *
     * @param array $datas
     * @return bool
     */
    public static function createDataProductEncryptAccessLog($datas = [])
    {
        $log = new DataProductEncryptAccessLog();
        $log->user_id = $datas['userId'];
        $log->username = $datas['username'];
        $log->mobile = $datas['mobile'];
        $log->platform_id = $datas['platformId'];
        $log->platform_product_id = $datas['productId'];
        $log->platform_product_name = $datas['product']['product_name'];
        $log->apply_url = $datas['apply_url'];
        $log->channel_no = $datas['channel_no'];
        $log->is_new_user = $datas['is_new_user'];
        $log->feedback_message = isset($datas['feedback_message']) ? $datas['feedback_message'] : '';
        $log->complete_degree = isset($datas['complete_degree']) ? $datas['complete_degree'] : '';
        $log->qualify_status = isset($datas['qualify_status']) ? $datas['qualify_status'] : 99;
        $log->period_type = isset($datas['period_type']) ? $datas['period_type'] : '0';
        $log->period = isset($datas['period']) ? $datas['period'] : '';
        $log->amount_min = isset($datas['amount_min']) ? $datas['amount_min'] : 0;
        $log->amount_max = isset($datas['amount_max']) ? $datas['amount_max'] : 0;
        $log->success_rate = isset($datas['success_rate']) ? $datas['success_rate'] : 0;
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->create_at = date('Y-m-d H:i:s', time());
        $log->create_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 合作贷产品立即申请信息
     *
     * @param array $data
     * @return array
     */
    public static function fetchCooperateWebsiteUrl($data = [])
    {
        $product = CooperateProduct::from('sd_cooperate_product as cp')
            ->select(['cp.id', 'cp.url as h5_url', 'cp.abut_switch', 'p.type_nid', 'p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.type_nid', 'p.product_h5_url', 'p.official_website as product_official_website'])
            ->join('sd_platform_product as p', 'p.platform_product_id', '=', 'cp.product_id')
            ->where(['cp.is_delete' => 0, 'cp.product_id' => $data['productId'], 'cp.type_id' => $data['typeId']])
            ->first();


        return $product ? $product->toArray() : [];
    }
}