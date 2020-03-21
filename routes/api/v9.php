<?php

$router->group(['prefix' => 'v9', 'namespace' => 'V9', 'middleware' => ['auth']], function ($router) {

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {

        //产品列表 & 速贷大全筛选
        $router->post('search', ['uses' => 'ProductController@search']);
        $router->post('recommends', ['uses' => 'ProductController@recommends']);
        $router->post('detail', ['uses' => 'ProductController@detail']);
        $router->post('apply', ['uses' => 'ProductController@apply']);
        $router->post('seriesLogin', ['uses' => 'ProductController@seriesLogin']);
        $router->post('vipCount', ['uses' => 'ProductController@vipCount']);
    });

    /**
     *  user API
     */
    $router->group(['prefix' => 'user'], function ($router) {
        // 获取用户类型
        $router->post('vipType', ['uses' => 'UserController@vipType']);

        // 购买烈熊会员
        $router->post('buyVip', ['uses' => 'UserController@buyVip']);

        // 烈熊会员卡列表
        $router->post('cardList', ['uses' => 'UserController@cardList']);

        // 用户连登福利
        $router->post('loginWelfare', ['uses' => 'UserController@loginWelfare']);

        // 用户动态
        $router->post('feed', ['uses' => 'UserController@feed']);

        // 用户信息
        $router->post('info', ['uses' => 'UserController@info']);
    });


    /**
     *  Product API
     */
    $router->group(['prefix' => 'products/test'], function ($router) {
        //产品列表 & 速贷大全筛选
        $router->post('main', ['uses' => 'ProductTestController@main']);
        $router->post('main/new', ['uses' => 'ProductTestController@mainNew']);
        $router->post('main/click', ['uses' => 'ProductTestController@mainClick']);
        $router->post('recommends', ['uses' => 'ProductTestController@recommends']);
        $router->post('good', ['uses' => 'ProductTestController@good']);
    });
});

