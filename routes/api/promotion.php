<?php

$router->group(['prefix' => 'promotion', 'namespace' => 'Promotion', 'middleware' => ['cros']], function ($router) {

    /**
     *  Auth API
     */
    $router->group(['prefix' => 'auth'], function ($router) {

        //联登
        $router->post('login', ['uses' => 'AuthController@login']);
    });

    /**
     *  Test API
     */
    $router->group(['prefix' => 'test'], function ($router) {

        //测试
        $router->get('', ['uses' => 'TestController@fetchSudaiUrl']);
    });

});

