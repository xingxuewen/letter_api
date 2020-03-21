<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 系统配置模块中使用的常量
 */
class BannersConstant extends AppConstant
{
    //广告 default
    const BANNER_TYPE_BANNER = 'default';
    //速贷推荐 banner_recommend
    const BANNER_TYPE_RECOMMEND = 'banner_recommend';
    //账单导入广告
    const BANNER_BILL_IMPORT = 'banner_bill_import';
    //广告轮播 - V1
    const BANNER_TYPE_NEW_BANNER = 'banner';
    //广告轮播 - V2
    const BANNER_TYPE_BANNER_CAROUSEL = 'banner_carousel';

    //会员中心广告
    const BANNER_TYPE_VIP_CENTER = 'banner_vip_center';
    // V2 - 会员中心 - 更改图片
    const BANNER_WELFARE_VIP_CENTER = 'banner_welfare_vip_center';
    // V3 - 会员中心 - 更改图片
    const BANNER_VIP_CENTER_WELFARES = 'banner_vip_center_welfares';
    //极速贷banner
    const BANNER_QUICKLOAN_TYPE = 'banner_quickloan';

    //热门贷款 hot_loan 2
    const BANNER_CREDIT_CARD_TYPE_HOT_LOAN = 'hot_loan';
    //分类专题 default 1
    const BANNER_CREDIT_CARD_TYPE_SPECIAL = 'default';
    //新分类专题 special 3
    const BANNER_CREDIT_CARD_TYPE_NEW_SPECIAL = 'special';
    //第三版 分类专题
    const BANNER_CREDIT_CARD_TYPE_THIRD_EDITION_SPECIAL = 'third_edition_special';
    //第四版 分类专题  轮播样式 3.1.1
    const BANNER_CAROUSEL_SPECIAL = 'special_carousel';
    //分类专题 - V5
    const BANNER_SPECIAL_V5 = 'special_carousel_view';

    //置顶分类专题
    const BANNER_SPECIAL_TOP = 'special_top';
    //置顶分类专题 - V2
    const BANNER_SPECIAL_TOP_V2 = 'special_top_view';
    //置顶分类专题 - V3
    const BANNER_SPECIAL_TOPS = 'special_tops';

    //第二版 速贷推荐
    const BANNER_CREDIT_CARD_TYPE_SECOND_EDITION_RECOMMEND = 'second_edition_recommend';
    //异形广告
    const BANNER_SPECIAL_SHAPED = 'banner_special_shaped';
    //异形广告 - V2
    const BANNER_SPECIAL_SHAPED_V2 = 'banner_special_shapeds';

    //置顶专题标识
    const BANNER_SPECIAL_TOP_SIGN = 'specialtop';
    //分类专题标识
    const BANNER_SPECIAL_SIGN = 'special';

    //极速贷图片配置
    const BANNER_CONFIG_QUICKLOAN = 'quickloan';

    //连登解锁
    const BANNER_UNLOCK_LOGIN_TYPE = 'banner_unlock_login';
    //连登解锁
    const BANNER_UNLOCK_LOGIN_TYPE_325 = 'banner_unlock_login_325';
    //会员独家
    const  BANNER_TYPE_MEMBERSHIP = 'banner_membership';

    //连登集合
    const BANNER_UNLOCK_LOGIN_NIDS = [
        'unlock_new', 'unlock_one_day', 'unlock_two_days', 'unlock_three_days',
    ];
    //连登解锁新用户
    const BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW = 'unlock_new';
    //连登解锁新用户325
    const BANNER_UNLOCK_LOGIN_NID_UNLOCK_NEW_325 = 'unlock_new_325';
    //新用户解锁产品
    const BANNER_UNLOCK_LOGIN_NID_NEW_USER_UNLOCK_PRO = 'new_user_unlock_pro';

    //首页热门推荐产品标识
    const BANNER_UNLO_LOGIN_PRO_RECOMMEND_SIGN = 'recommend_unlock_login';
    //解锁连登123 各品类产品固排
    const BANNER_UNLOCK_LOGIN_SORT = ['jiesuan', 'neibu', 'xianliang'];
}
