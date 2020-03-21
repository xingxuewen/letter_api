<?php

$router->group(['prefix' => 'v5', 'namespace' => 'V5', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *  Banners API
     */
    $router->group(['prefix' => 'banners'], function ($router) {
        //分类专题
        $router->get('special', ['uses' => 'BannersController@fetchSpecials']);
    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail', 'validate:calculator'], 'uses' => 'ProductController@fetchCalculators']);
        //产品详情第一部分 - 速贷大数据
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductDatas']);
        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //产品详情细节 第二部分
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductLike']);
        //首页推荐产品
        $router->get('recommends', ['uses' => 'ProductController@fetchPromotions']);
        //首页推荐产品 与用户相关
        $router->get('recommends/about/user', ['uses' => 'ProductController@fetchPromotionsAboutUser']);

    });


});

