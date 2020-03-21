<?php

$router->group(['prefix' => 'shadow', 'namespace' => 'Shadow', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    $router->group(['prefix' => 'v1', 'namespace' => 'V1'], function ($router) {

        /**
         * Users API
         */
        $router->group(['prefix' => 'users'], function ($router) {
            //身份验证
            $router->group(['prefix' => 'identity'], function ($router) {
                //修改实名认证
                $router->post('certifica', ['middleware' => ['auth'], 'uses' => 'UserIdentityController@updateRealnameInfo']);
                //查询实名认证
                $router->get('certifica', ['middleware' => ['auth'], 'uses' => 'UserIdentityController@fetchRealnameAndIdcard']);
                //百款聚到 - 修改实名认证
                $router->post('oneloan/certifica', ['middleware' => ['auth', 'validate:verifyMobileInfo3', 'validate:nid'], 'uses' => 'UserIdentityController@updateFakeRealnameInfo']);
            });
        });


        /**
         * Product API
         */
        $router->group(['prefix' => 'product'], function ($router) {
            // 速贷大全列表
            $router->get('lists', ['uses' => 'ProductController@fetchProductsOrSearchs']);
            // 速贷大全列表 & 速贷大选筛选
            $router->get('filters', ['uses' => 'ProductController@fetchProductsOrFilters']);
            // 马甲包产品申请统计
            $router->get('application', ['middleware' => ['auth', 'validate:oauth'], 'uses' => 'ProductController@apply']);
            //产品详情细节 第二部分
            $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailOther']);
            //立即申请 - 推荐标签匹配产品列表
            $router->get('tag/matchs', ['middleware' => ['auth', 'validate:productdetail'], 'uses' => 'ProductController@fetchProductTagMatchs']);

        });

        /**
         *  Auth API
         */
        $router->group(['prefix' => 'auth'], function ($router) {
            // 快速登陆
            $router->post('quicklogin', ['middleware' => ['validate:quicklogin'], 'uses' => 'AuthController@quickLogin']);
        });

        /**
         * Sms API
         */
        $router->group(['prefix' => 'sms'], function ($router) {
            //注册短信验证码
            $router->post('register', ['middleware' => ['validate:code'], 'uses' => 'SmsController@register']);
        });

        /**
         * Zhima API
         */
        $router->group(['prefix' => 'zhima'], function ($router) {
            $router->get('', function () {
                return view('zhima');
            });

            $router->post('query', [
                'as' => 'shadow.zhima',
                'uses' => 'ZhimaController@query',
            ]);

            // 回调地址
            $router->get('score', [
                'as' => '',
                'uses' => 'ZhimaController@getScore',
            ]);
        });


        /**
         *  Banks API
         *  信用卡
         */
        $router->group(['prefix' => 'banks'], function ($router) {
            //信用卡
            $router->group(['prefix' => 'creditcard'], function ($router) {
                //信用卡
                $router->get('', ['uses' => 'CreditcardController@fetchCreditcard']);
                //信用卡点击
                $router->get('application', ['uses' => 'CreditcardController@fetchCreditcardUrl']);
                //信用卡筛选头部
                $router->get('title', ['uses' => 'CreditcardController@fetchSelectTitles']);
                //信用卡筛选
                $router->get('filters', ['middleware' => ['validate:deviceIdMin'], 'uses' => 'CreditcardController@fetchCreditCardSearches']);

            });

        });

        /**
         *  Oauth API
         */
        $router->group(['prefix' => 'oauth'], function ($router) {
            //立即申请撞库判断
            $router->get('judge', ['middleware' => ['validate:productdetail'], 'uses' => 'OauthController@fetchProductIsAuthenEtc']);

            //一键选贷款-申请
            $router->group(['prefix' => 'spread'], function ($router) {
                //立即申请
                $router->get('application', ['uses' => 'OauthController@fetchSpreadUrl']);
            });

        });


        /**
         * Spread API
         */
        $router->group(['prefix' => 'spread'], function ($router) {
            $router->get('info', ['uses' => 'UserSpreadController@fetchOneloanInfo']);
        });

        /**
         *  Data API
         */
        $router->group(['prefix' => 'data'], function ($router) {
            //区域点击统计
            $router->post('region', ['middleware' => ['validate:clickSource'], 'uses' => 'DataController@createUserRegionLog']);
        });

    });

    /**
     * V2 API
     */
    $router->group(['prefix' => 'v2', 'namespace' => 'V2'], function ($router) {
        /**
         * Product API
         */
        $router->group(['prefix' => 'product'], function ($router) {
            // 速贷大全首页推荐产品
            $router->get('recommend', ['uses' => 'ProductController@fetchRecommendProducts']);
            // 速贷大全列表
            $router->get('lists', ['uses' => 'ProductController@fetchProducts']);
            //产品详情第一部分 - 速贷大数据
            $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductDatas']);
            // 产品详情 - 产品特色
            $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductLike']);

        });

    });

});