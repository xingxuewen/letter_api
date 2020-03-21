<?php

$router->group(['prefix' => 'v8', 'namespace' => 'V8', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {

        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);

    });


});

