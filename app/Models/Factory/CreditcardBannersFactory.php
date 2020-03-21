<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\BankBanner;
use App\Models\Orm\BankBannerType;
use App\Models\Orm\BankCreditcardUsageType;
use App\Models\Orm\CreditCardBanner;
use App\Models\Orm\CreditCardBannerRel;

/**
 * Class CreditcardBannersFactory
 * @package App\Models\Factory
 * 信用卡图片工厂
 */
class CreditcardBannersFactory extends AbsModelFactory
{
    /**
     * @param $typeNid
     * @return array
     * @status 是否显示 1显示, 0不显示
     * 银行轮播图片类型
     */
    public static function fetchBankBannerTypeId($typeNid)
    {
        $bannerTypes = BankBannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $bannerTypes ? $bannerTypes->id : '';
    }

    /**
     * @param $bannerTypeId
     * @return array
     * @status  图片使用状态 0未使用,1使用中
     */
    public static function fetchBankBanners($bannerTypeId)
    {
        $time = date('Y-m-d H:i:s', time());
        $banners = BankBanner::where('end_time', '>', $time)
            ->where(['status' => 1, 'type_id' => $bannerTypeId])
            ->orderBy('position')
            ->limit(5)
            ->select('img_link', 'img_url', 'name')
            ->get()->toArray();
        return $banners ? $banners : [];
    }

    /**
     * @param $typeNid
     * @return string
     * @status 是否显示, 1 显示, 0 不显示
     * 根绝typeNid 获取 name
     */
    public static function fetchUsageTypeNameByTypeNid($typeNid)
    {
        $name = BankCreditcardUsageType::select('name')
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $name ? $name->name : '';
    }

    /**
     * 分类专题
     *  相似推荐专题
     *  只限制3个,小于3个不展示
     *
     * @param array $data
     * @return array
     */
    public static function fetchLikeSpecials($data = [])
    {
        $specials = CreditCardBannerRel::from('sd_banner_credit_card_rel as rel')
            ->select(['rel.credit_card_id', 'rel.reco_img'])
            ->addSelect(['card.type_id', 'card.type_nid', 'card.app_link', 'card.h5_link', 'card.title', 'card.subtitle', 'type.type_nid as nid'])
            ->join('sd_banner_credit_card as card', 'rel.credit_card_id', '=', 'card.id')
            ->join('sd_banner_credit_card_type as type', 'card.type_id', '=', 'type.id')
            ->where(['rel.status' => 1, 'rel.card_id' => $data['specialId'], 'card.ad_status' => 0, 'type.status' => 1])
            ->where('card.status', '<>', 9)
            ->orderBy('rel.position_sort', 'asc')
            ->orderBy('rel.id', 'desc')
            ->limit($data['likeLimit'])
            ->get()->toArray();

        return count($specials) < $data['likeLimit'] ? [] : $specials;
    }
}