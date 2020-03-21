<?php

namespace App\Helpers;

use App\Services\AppService;

class LinkUtils
{

    public static function getRand()
    {
        $rand = mt_rand(500000, 1200000);
        return $rand;
    }

    // 资讯链接
    public static function appLink($id)
    {
        return AppService::H5_URL . '/html/consultApp2.2.html?newsId=' . $id;
    }

    // 产品分享
    public static function productShare($id)
    {
        //http://h5.sudaizhijia.com/#/product_detail?productId=571
        return AppService::H5_URL . '/#/product_detail?productId=' . $id;
    }

    // 第三版 2.9.0 产品分享
    public static function thirdEditionProductShare($id)
    {
        return AppService::H5_URL . '/#/product_detail?productId=' . $id;
    }

    //分享落地页
    public static function shareLanding($invite_code = '')
    {
        //http://h5.sudaizhijia.com/#/event/landing
        return AppService::H5_URL . '/#/event/landing?sd_invite_code=' . $invite_code;
    }

    // 分享加积分
    /*    public static function shareAddScores($userId = 0)
        {
            return AppService::H5_URL . '/html/mine_aboutour.html?userId=' . $userId;
        }*/

    // 积分页面单独分享
    public static function shareOnlyLink($userId = 0)
    {
        return AppService::EVENT_URL . '/m/landing/index.html?userId=' . $userId . '&winzoom=1';
    }

    // 关于我们
    public static function shareOur()
    {
        //http://h5.sudaizhijia.com/#/setup_aboutus?use=app
        return AppService::H5_URL . '/#/setup_aboutus?use=app';
    }

    //关于现金提现规则说明
    public static function getAccountRule()
    {
        // http://h5.sudaizhijia.com/#/cashrule
        return AppService::H5_URL . '/#/cashrule?use=app';
    }

    //android 调 h5 的帮助中心连接地址
    public static function getHelpsToAndroid()
    {
        // http://h5.sudaizhijia.com/#/help
        return AppService::H5_URL . '/#/help';
    }

    //商务合作
    public static function BusinessCooperation()
    {
        //http://h5.sudaizhijia.com/#/setup_cooperation?use=app
        return AppService::H5_URL . '/#/setup_cooperation?use=app';
    }

    //身份证认证——用户协议
    /*    public static function getAgreement()
        {
            return AppService::H5_URL . '/html/agreement.html';
        }*/

    //信用卡 —— 支持银行列表
    public static function quotaCreditCardBankLink()
    {
        //http://h5.sudaizhijia.com/#/support_bank?status=credit
        return AppService::H5_URL . '/#/support_bank?status=credit';
    }

    //储蓄卡 —— 支持银行列表
    public static function quotaSavingCardBankLink()
    {
        //http://h5.sudaizhijia.com/#/support_bank?status=saving
        return AppService::H5_URL . '/#/support_bank?status=saving';
    }

    //信用卡 —— 支持银行列表
    public static function quotaCreditCardBankLinkHj()
    {
        //http://h5.sudaizhijia.com/#/support_bank?status=credit
        return AppService::H5_URL . '/#/support_bank_hj?status=credit';
    }

    //储蓄卡 —— 支持银行列表
    public static function quotaSavingCardBankLinkHj()
    {
        //http://h5.sudaizhijia.com/#/support_bank?status=saving
        return AppService::H5_URL . '/#/support_bank_hj?status=saving';
    }

}
