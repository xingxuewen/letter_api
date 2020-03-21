<?php

$router->group(['prefix' => 'view', 'namespace' => 'View', 'middleware' => ['analysis', 'sign', 'cros']], function ($router) {
    /*
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {

        //身份认证
        $router->group(['prefix' => 'identity'], function ($router) {
            //身份认证——认证协议
            $router->get('agreement', ['uses' => 'UserController@fetchIdentityAgreement']);
            //速贷之家——会员协议
            $router->get('membership', ['uses' => 'UserController@fetchMembershipAgreement']);
            //速贷之家APP用户使用协议
            $router->get('use', ['uses' => 'UserController@fetchUseAgreement']);
            // 畅行天下升级版保险文档说明
            $router->get('insurance', ['uses' => 'UserController@fetchChangxingtianxiaAgreement']);
            //北京野二APP用户使用协议
            $router->get('yeer/agreement', ['uses' => 'UserController@fetchYeerAgreement']);
        });

        //信用报告
        $router->group(['prefix' => 'report'], function ($router) {
            //信用报告——个人报告查询授权书
            $router->get('agreement', ['uses' => 'UserReportController@fetchReportAgreement']);
            //信用报告样本
            $router->get('sample', ['uses' => 'UserReportController@fetchReportSample']);
            //速贷之家自定义芝麻跳转地址
            $router->get('zhima/url', ['uses' => 'UserReportController@fetchZhimaUrl']);
        });

        //贷款账户

        $router->group(['prefix' => 'bill'], function ($router) {
            //账户数据分析
            $router->get('analysis', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillAnalysis']);
            //账单明细
            $router->get('creditcard/bills', ['middleware' => ['auth', 'validate:billCreditcardId'], 'uses' => 'UserBillController@fetchCreditcardBills']);
            //导入信用卡账单结果列表
            $router->get('import/results', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillImportResults']);
        });


        // VIP
        $router->group(['prefix' => 'vip'], function ($router) {
            //会员中心
            $router->get('centers', ['uses' => 'UserVipController@vipCenter']);
            //会员协议
            $router->get('agreement', ['uses' => 'UserVipController@fetchVipAgreement']);
        });


    });

    /**
     *  Help API
     */
    $router->group(['prefix' => 'help'], function ($router) {
        //设置 - 协议列表
        $router->get('agreements', ['uses' => 'HelpController@fetchAgreements']);
        //设置 - 协议详情
        $router->get('agreement', ['middleware' => ['validate:id'], 'uses' => 'HelpController@fetchAgreement']);
    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'product'], function ($router) {

        //置顶分类专题
        $router->get('specials', ['middleware' => ['validate:special'], 'uses' => 'ProductController@fetchTopSpecials']);
        //分类专题
        $router->get('specials/lists', ['middleware' => ['validate:special'], 'uses' => 'ProductController@fetchSpecials']);
        //异形banner产品
        $router->get('shape', ['uses' => 'ProductController@fetchShapeds']);
        //闪电下款
        $router->get('lighting', ['uses' => 'ProductController@fetchLightningLoans']);
        //本周放款王
        $router->get('king', ['uses' => 'ProductController@fetchKingLoans']);
        //解锁联登产品
        $router->get('unlock', ['middleware' => ['validate:unlockLoginId'], 'uses' => 'ProductController@fetchUnlockLoginProducts']);


    });

    /**
     *  Oneloan API
     */
    $router->group(['prefix' => 'oneloan'], function ($router) {
        //基础信息
        $router->get('basic', ['uses' => 'OneloanController@fetchBasic']);
        //完整信息
        $router->get('full', ['uses' => 'OneloanController@fetchFull']);
        //结果
        $router->get('result', ['uses' => 'OneloanController@fetchResult']);
        //公共
        $router->get('common', ['uses' => 'OneloanController@fetchCommon']);
        //协议
        $router->get('agreement', ['uses' => 'OneloanController@fetchAgreement']);
        //城市列表
        $router->get('citys', ['uses' => 'OneloanController@fetchCitys']);

        /**
         *  Product API
         */
        $router->group(['prefix' => 'products'], function ($router) {
            //结果页面产品列表
            $router->get('', ['uses' => 'OneloanProductController@fetchResultProducts']);
            //立即申请页面
            $router->get('application', ['uses' => 'OneloanProductController@fetchApplyView']);
        });
    });


    /**
     * API-2
     */
    $router->group(['prefix' => 'v2'], function ($router) {

        /**
         * Users API
         */
        $router->group(['prefix' => 'users'], function ($router) {

            //身份认证
            $router->group(['prefix' => 'identity'], function ($router) {
                //身份认证——认证协议
                $router->get('agreement', ['uses' => 'UserController@fetchIdentityAgreementByParam']);
                //速贷之家APP用户使用协议
                $router->get('use', ['uses' => 'UserController@fetchUseAgreementByParam']);
                //速贷之家——会员协议
                $router->get('membership', ['uses' => 'UserController@fetchMembershipAgreementByParam']);
            });

            //信用报告
            $router->group(['prefix' => 'report'], function ($router) {
                //信用报告——个人报告查询授权书
                $router->get('agreement', ['uses' => 'UserReportController@fetchReportAgreementByParam']);
            });

        });

        /**
         *  Product API
         */
        $router->group(['prefix' => 'product'], function ($router) {
            //分类专题
            $router->get('specials', ['middleware' => ['validate:special'], 'uses' => 'ProductController@fetchTopSpecialsSortV2']);

        });


        /**
         *  Help API
         */
        $router->group(['prefix' => 'help'], function ($router) {
            //设置 - 协议列表
            $router->get('agreements', ['uses' => 'HelpController@fetchAgreementsByParam']);
            //设置 - 协议详情
            $router->get('agreement', ['middleware' => ['validate:id'], 'uses' => 'HelpController@fetchAgreementByParam']);
        });

    });
});

/**
 *  不需要sign中间件
 */
$router->group(['prefix' => 'view', 'namespace' => 'View', 'middleware' => ['analysis', 'cros']], function ($router) {

    // Users
    $router->group(['prefix' => 'users'], function ($router) {

        //Report
        $router->group(['prefix' => 'report'], function ($router) {
            //信用报告详情
            $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchReportinfo']);
        });
    });
});




