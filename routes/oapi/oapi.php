<?php
$router->group(['prefix' => 'oapi', 'namespace' => 'Oapi', 'middleware' => []], function ($router) {


    /**
     * 通知、回调
     */
    $router->group(['prefix' => 'notice'], function ($router) {
        // 烈熊支付通知、退款通知
        $router->post('liexiong', ['uses' => 'NoticeController@liexiong']);
    });
});
