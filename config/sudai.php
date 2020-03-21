<?php

return [
    //-------------------合作方对接的------------------------//
    'des_key' => 'u8c9kToN',
    //-----------------七牛图片储存配置-----------------------//
    'qiniu' => [
        'ak' => 'U43-nZtveZk30g2QaBQdYKo_C-HeSkOZnznppUYr',
        'sk' => 'Zmvxi0RJ1dh2VcA2Fmro3KcbYsWQ2zHEIdsvbKuD',
        /*
          'bucket' => 'sudaizhijia-test',
          'domain' => 'sudaizhijia-test.qiniudn.com',
          'prefix' => '',
          'baseurl' => 'http://obd78f18t.bkt.clouddn.com/', //速贷之家测试线七牛地址
         */
        'bucket' => 'sudaizhijia-online',
        'prefix' => '',
        'domain' => 'sudaizhijia-online.qiniudn.com',
        'baseurl' => (\App\Helpers\Utils::isiOS() || \App\Helpers\Utils::isMAPI() ) ? 'http://obd7ty4wc.bkt.clouddn.com/': 'http://image.sudaizhijia.com/', //速贷之家正式线七牛地址
        //'baseurl' => 'http://obd7ty4wc.bkt.clouddn.com/', //速贷之家正式线七牛地址

    ],
    'imageUrl' => 'http://image.sudaizhijia.com/',
    //---------------------盐值-----------------------------//
    'salt' => [
        'salt' => '7edc32332fe11fb3c14e76408196d86ad206e9a3',
    ],
];
