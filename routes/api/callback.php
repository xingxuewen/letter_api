<?php

$router->group(['prefix' => 'v1', 'namespace' => 'V1', 'middleware' => ['cros']], function ($router) {


    /**
     *  Users API
     */
    $router->group(['prefix' => 'callback'], function ($router) {

        // 用户银行卡绑定
        $router->group(['prefix' => 'payment'], function ($router) {
            //易宝回调
            $router->get('yibao/test', ['uses' => 'PaymentCallbackController@test']);
            //同步回调
            $router->get('yibao/syncallbacks', ['uses' => 'PaymentCallbackController@yiBaoSynCallBack']);
            //异步回调
            $router->post('yibao/asyncallbacks', ['uses' => 'PaymentCallbackController@yiBaoAsynCallBack']);

            //同步回调
            $router->post('yibao/syncallbacks', ['uses' => 'PaymentCallbackController@yiBaoSynCallBack']);
            //异步回调
            $router->get('yibao/asyncallbacks', ['uses' => 'PaymentCallbackController@yiBaoAsynCallBack']);

//            //汇聚同步回调
//            $router->any('huiju/syncallbacks', ['uses' => 'PaymentCallbackController@huiJuSynCallBack']);
//            //汇聚异步回调
//            $router->any('huiju/asyncallbacks', ['uses' => 'PaymentCallbackController@huiJuAsynCallBack']);

            $router->addRoute(['GET', 'POST'], 'huiju/syncallbacks', ['uses' => 'PaymentCallbackController@huiJuSynCallBack']);

            $router->addRoute(['GET', 'POST'], 'huiju/asyncallbacks', ['uses' => 'PaymentCallbackController@huiJuAsynCallBack']);
            // by xuyj
            $router->addRoute(['GET', 'POST'], 'huiju/asyncallbacks_quick', ['uses' => 'PaymentCallbackController@huiJuAsynCallBack_quick']);
            // by xuyj v3.2.3 判断当前卡是信用卡还是储蓄卡
            $router->addRoute(['GET', 'POST'], 'cardtype', ['uses' => 'PaymentCallbackController@cardtype']);
            // by xuyj v3.2.3 去汇聚查询该订单是否生效
            $router->addRoute(['GET', 'POST'], 'checkorderstatus', ['uses' => 'PaymentCallbackController@checkorderstatus']);

            // by xuyj v3.2.3 返回汇聚支持的银行名
            $router->addRoute(['GET', 'POST'], 'surportBank', ['uses' => 'PaymentCallbackController@surportBank']);
            // by xuyj v3.2.3 汇聚支付回调----微信小程序回调
            $router->addRoute(['GET', 'POST'], 'huiju/asyncallbacks_wechat', ['uses' => 'PaymentCallbackController@asyncallbacks_wechat']);

        });
    });

});

