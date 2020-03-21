<?php
$router->group(['prefix' => 'view/v2', 'namespace' => 'View\V2', 'middleware' => ['analysis', 'sign', 'cros']], function ($router) {

    /*
   *  Users API
   */
    $router->group(['prefix' => 'users'], function ($router) {

        // VIP
        $router->group(['prefix' => 'vip'], function ($router) {
            //会员中心
            $router->get('centers', ['uses' => 'UserVipController@vipCenter']);
        });
    });


    /**
     *  Product API
     */
    $router->group(['prefix' => 'product'], function ($router) {
        //异形banner产品
        $router->get('shape', ['uses' => 'ProductController@fetchShapeds']);
        //解锁联登产品
        $router->get('unlock', ['middleware' => ['validate:unlockLoginId'], 'uses' => 'ProductController@fetchUnlockLoginProducts']);
    });
});
