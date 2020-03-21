<?php

$router->group(['prefix' => 'v6', 'namespace' => 'V6', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {

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
