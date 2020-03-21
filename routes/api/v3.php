<?php

$router->group(['prefix' => 'v3', 'namespace' => 'V3', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {
    /*
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {
        //修改用户名
        $router->post('username', ['middleware' => ['validate:username', 'auth'], 'uses' => 'UserController@updateUsername']);
        //用户账户信息
        $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserController@fetchUserinfo']);

        $router->get('info_new', ['middleware' => ['auth'], 'uses' => 'UserController@fetchUserinfo_new']);

        // VIP
        $router->group(['prefix' => 'vip'], function ($router) {
            //会员中心
            $router->get('centers', ['uses' => 'UserVipController@vipCenter']);
        });

    });

    /**
     *  Banners API
     */
    $router->group(['prefix' => 'banners'], function ($router) {
        //广告轮播
        $router->get('', ['uses' => 'BannersController@fetchBanners']);
        //置顶分类专题
        $router->get('special/tops', ['uses' => 'BannersController@fetchSpecialTops']);
        //分类专题
        $router->get('special', ['uses' => 'BannersController@fetchSpecialsAndRecommends']);
        //会员中心广告
        $router->get('vip/center', ['uses' => 'BannersController@fetchVipCenterBanner']);
    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail', 'validate:calculator'], 'uses' => 'ProductController@fetchCalculators']);
        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //产品详情第一部分
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailPartOne']);
        //产品详情细节 第二部分
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailOther']);

        //首页推荐产品
        $router->get('recommends', ['uses' => 'ProductController@fetchPromotions']);

    });

    /**
     *  ProductSearch API
     */
    $router->group(['prefix' => 'product/searchs'], function ($router) {
        //搜索热刺列表
        $router->get('hots', ['uses' => 'ProductSearchController@fetchHots']);
        //搜索结果列表
        $router->get('', ['middleware' => ['validate:productsearch'], 'uses' => 'ProductSearchController@fetchSearchs']);
    });

    /**
     *  Comment API
     */
    $router->group(['prefix' => 'comment'], function ($router) {
        //查询评论内容
        $router->get('before', ['middleware' => ['validate:productdetail', 'auth'], 'uses' => 'CommentController@fetchCommentsBefore']);
        //修改评论内容
        $router->post('', ['middleware' => ['validate:comment', 'auth'], 'uses' => 'CommentController@createOrUpdateComment']);
        //评论星星值
        $router->get('score', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentCountAndScore']);
        //最热评论
        $router->get('hots', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentHots']);
        //所有评论
        $router->get('comments', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchComments']);
    });

    /**
     *  Favourite API
     */
    $router->group(['prefix' => 'favourite'], function ($router) {
        //产品——收藏列表
        $router->get('collections', ['middleware' => ['auth'], 'uses' => 'FavouriteController@fetchCollectionLists']);

    });

    /**
     * Spread API
     */
    $router->group(['prefix' => 'spread'], function ($router) {
        //百款聚到
        $router->get('info', ['uses' => 'UserSpreadController@fetchOneloanInfo']);
    });


    /**
     * Push API
     */
    $router->group(['prefix' => 'push'], function ($router) {
        //任务弹窗
        $router->get('popup', ['middleware' => ['validate:popup'], 'uses' => 'PushController@fetchPopup']);
    });

});

