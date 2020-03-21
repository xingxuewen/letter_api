<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Event;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\V1\UserLoginEvent' => [
            'App\Listeners\V1\UserLoginListener',
        ],
        'App\Events\V1\UserRegEvent' => [
            'App\Listeners\V1\UserRegCreditListener',
            //'App\Listeners\V1\UserRegNoticeListener',
            'App\Listeners\V1\UserRegCountListener',
        ],
        'App\Events\V1\UserPushEvent' => [
            'App\Listeners\V1\UserPushListener',
        ],
        //idfa激活回调
        'App\Events\V1\UserIdfaEvent' => [
            'App\Listeners\V1\UserIdfaListener',
        ],
        //用户连登事件
        'App\Events\V1\UserUnlockLoginEvent' => [
            'App\Listeners\V1\UserUnlockLoginListener',
        ],
        //统计事件
        'App\Events\V1\DataProductEvent' => [
            'App\Listeners\V1\DataProductExposureListener', //产品曝光统计
        ],
        //连登统计
        'App\Events\V1\DataBannerUnlockEvent' => [
            'App\Listeners\V1\DataBannerUnlockListener', //连登点击统计
        ],

        // 信用卡申请统计　&　申请增量监听器
        'App\Events\V1\CardApplyEvent' => [
            'App\Listeners\V1\CardApplyListener',
        ],
        //加积分事件
        'App\Events\V1\AddIntegralEvent' => [
            'App\Listeners\V1\AddIntegralListener',
        ],
        //马甲事件
        'App\Events\Shadow\UserShadowEvent' => [
            'App\Listeners\Shadow\UserShadowListener',
        ],
        // 马甲产品立即申请统计事件
        'App\Events\Shadow\ShadowProductApplyEvent' => [
            'App\Listeners\Shadow\ShadowProductApplyListener',
        ],
        // 马甲信用卡立即申请统计事件
        'App\Events\V1\ShadowCardApplyEvent' => [
            'App\Listeners\V1\ShadowCardApplyListener',
        ],

        //标签规则推荐产品创建流水事件监听
        'App\Events\V1\DataProductTagLogEvent' => [
            'App\Listeners\V1\DataProductTagLogListener',
        ],

        // 用户赠险事件
        'App\Events\V1\UserInsuranceEvent' => [
            'App\Listeners\V1\UserInsuranceListener',
        ],

        // 用户推广事件
        'App\Events\V1\UserSpreadEvent' => [
            //'App\Listeners\V1\UserLoanListener', // 助贷网推广
            'App\Listeners\V1\UserNewLoanListener', // 新一贷推广
            'App\Listeners\V1\UserFinanceListener', // 小小金融推广
            'App\Listeners\V1\UserPaipaidaiListener', // 拍拍贷推广
            'App\Listeners\V1\UserOxygendaiListener', // 氧气贷推广

        ],
        // 推广统计事件
        'App\Events\V1\UserSpreadCountEvent' => [
            'App\Listeners\V1\UserSpreadCountListener',
        ],

        // 一键贷监听事件
        // 一键贷推广统计
        'App\Events\Oneloan\Partner\UserSpreadCountEvent' => [
            'App\Listeners\Oneloan\Partner\UserSpreadCountListener',
        ],

        // 黑牛保险
        'App\Events\Oneloan\Partner\UserInsuranceEvent' => [
            'App\Listeners\Oneloan\Partner\UserInsuranceListener',
        ],

        // 东方金融
        'App\Events\Oneloan\Partner\UserDongfangEvent' => [
            'App\Listeners\Oneloan\Partner\UserDongfangListener',
        ],

        // 小小金融
        'App\Events\Oneloan\Partner\UserFinanceEvent' => [
            'App\Listeners\Oneloan\Partner\UserFinanceListener',
        ],

        // 恒昌金融
        'App\Events\Oneloan\Partner\UserHengchangEvent' => [
            'App\Listeners\Oneloan\Partner\UserHengChangListener',
        ],

        // 厚本金融
        'App\Events\Oneloan\Partner\UserHoubenEvent' => [
            'App\Listeners\Oneloan\Partner\UserHoubenListener',
        ],

        // 助贷网
        'App\Events\Oneloan\Partner\UserLoanEvent' => [
            'App\Listeners\Oneloan\Partner\UserLoanListener',
        ],

        // 新一贷
        'App\Events\Oneloan\Partner\UserNewLoanEvent' => [
            'App\Listeners\Oneloan\Partner\UserNewLoanListener',
        ],

        // 氧气贷
        'App\Events\Oneloan\Partner\UserOxygendaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserOxygendaiListener',
        ],

        // 拍拍贷
        'App\Events\Oneloan\Partner\UserPaipaidaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserPaipaidaiListener',
        ],

        // 小小金融2
        'App\Events\Oneloan\Partner\UserXiaoxiaoEvent' => [
            'App\Listeners\Oneloan\Partner\UserXiaoxiaoListener',
        ],

        // 有利网
        'App\Events\Oneloan\Partner\UserYouliEvent' => [
            'App\Listeners\Oneloan\Partner\UserYouliListener',
        ],

        // 中腾信
        'App\Events\Oneloan\Partner\UserZhongtengxinEvent' => [
            'App\Listeners\Oneloan\Partner\UserZhongtengxinListener',
        ],

        // 你我贷
        'App\Events\Oneloan\Partner\UserNiwodaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserNiwodaiListener',
        ],

        //你我贷-秒啦
        'App\Events\Oneloan\Partner\UserMiaolaEvent' => [
            'App\Listeners\Oneloan\Partner\UserMiaolaListener',
        ],

        // 秒贷
        'App\Events\Oneloan\Partner\UserMiaodaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserMiaodaiListener',
        ],

        // 工银英
        'App\Events\Oneloan\Partner\UserGongyinyingEvent' => [
            'App\Listeners\Oneloan\Partner\UserGongyinyingListener',
        ],

        // 融时代
        'App\Events\Oneloan\Partner\UserRongshidaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserRongshidaiListener',
        ],

        // 恒易贷
        'App\Events\Oneloan\Partner\UserHengyiEvent' => [
            'App\Listeners\Oneloan\Partner\UserHengyidaiListener',
        ],

        //猴哥贷
        'App\Events\Oneloan\Partner\UserHougedaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserHougedaiListener',
        ],

        //春雨贷
        'App\Events\Oneloan\Partner\UserChunyuEvent' => [
            'App\Listeners\Oneloan\Partner\UserChunyuListener',
        ],

        //任信用
        'App\Events\Oneloan\Partner\UserRenxinyongEvent' => [
            'App\Listeners\Oneloan\Partner\UserRenxinyongListener',
        ],

        //智借车贷
        'App\Events\Oneloan\Partner\UserZhijiechedaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserZhijiechedaiListener',
        ],

        //房金所
        'App\Events\Oneloan\Partner\UserFangjinsuoEvent' => [
            'App\Listeners\Oneloan\Partner\UserFangjinsuoListener',
        ],

        //西伊
        'App\Events\Oneloan\Partner\UserXiyiEvent' => [
            'App\Listeners\Oneloan\Partner\UserXiyiListener',
        ],

        //财鸟
        'App\Events\Oneloan\Partner\UserCainiaoEvent' => [
            'App\Listeners\Oneloan\Partner\UserCainiaoListener',
        ],

        //吉宝贷
        'App\Events\Oneloan\Partner\UserJibaodaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserJibaodaiListener',
        ],
        //有利2
        'App\Events\Oneloan\Partner\UserYoulinewEvent' => [
            'App\Listeners\Oneloan\Partner\UserYoulinewListener',
        ],
        //展业王
        'App\Events\Oneloan\Partner\UserZhanyewangEvent' => [
            'App\Listeners\Oneloan\Partner\UserZhanyewangListener',
        ],
        //大智金服
        'App\Events\Oneloan\Partner\UserDazhiEvent' => [
            'App\Listeners\Oneloan\Partner\UserDazhiListener',
        ],
        //融贷
        'App\Events\Oneloan\Partner\UserRongdaiEvent' => [
            'App\Listeners\Oneloan\Partner\UserRongdaiListener',
        ],
        //意扬
        'App\Events\Oneloan\Partner\UserYiyangEvent' => [
            'App\Listeners\Oneloan\Partner\UserYiyangListener',
        ],
        //佳佳融
        'App\Events\Oneloan\Partner\UserJiajiarongEvent' => [
            'App\Listeners\Oneloan\Partner\UserJiajiarongListener',
        ],
        //贷款平台18
        'App\Events\Oneloan\Partner\UserSpreadEvent' => [
            'App\Listeners\Oneloan\Partner\UserInsuranceListener',   //黑牛
            'App\Listeners\Oneloan\Partner\UserLoanListener',        //助贷网
            'App\Listeners\Oneloan\Partner\UserNewLoanListener',     //新一贷
            'App\Listeners\Oneloan\Partner\UserOxygendaiListener',   //氧气贷
            'App\Listeners\Oneloan\Partner\UserPaipaidaiListener',   //拍拍贷
            'App\Listeners\Oneloan\Partner\UserDongfangListener',    //东方金融
            'App\Listeners\Oneloan\Partner\UserHengChangListener',  //恒昌金融
            'App\Listeners\Oneloan\Partner\UserHengyidaiListener',  //恒昌金融线下恒易贷
            'App\Listeners\Oneloan\Partner\UserHoubenListener',      //厚本金融
            'App\Listeners\Oneloan\Partner\UserFinanceListener',     //小小金融
            'App\Listeners\Oneloan\Partner\UserZhongtengxinListener',//中腾信
            'App\Listeners\Oneloan\Partner\UserYouliListener',       //有利
            'App\Listeners\Oneloan\Partner\UserXiaoxiaoListener',    //小小金融2新渠道
            'App\Listeners\Oneloan\Partner\UserNiwodaiListener',     //你我贷
            'App\Listeners\Oneloan\Partner\UserMiaolaListener',      //你我贷-秒啦
            'App\Listeners\Oneloan\Partner\UserMiaodaiListener',     //秒贷
            'App\Listeners\Oneloan\Partner\UserGongyinyingListener', //工银英
            'App\Listeners\Oneloan\Partner\UserRongshidaiListener',  //融时代
            'App\Listeners\Oneloan\Partner\UserHougedaiListener',   //猴哥贷
            'App\Listeners\Oneloan\Partner\UserChunyuListener',     //春雨
            'App\Listeners\Oneloan\Partner\UserRenxinyongListener',     //任信用
            'App\Listeners\Oneloan\Partner\UserZhijiechedaiListener',     //智借车贷
            'App\Listeners\Oneloan\Partner\UserFangjinsuoListener',     //房金所
            'App\Listeners\Oneloan\Partner\UserXiyiListener',//西伊
            'App\Listeners\Oneloan\Partner\UserCainiaoListener',//财鸟
            'App\Listeners\Oneloan\Partner\UserJibaodaiListener',//吉宝贷
            'App\Listeners\Oneloan\Partner\UserYoulinewListener',//有利2
            'App\Listeners\Oneloan\Partner\UserZhanyewangListener',//展业王
            'App\Listeners\Oneloan\Partner\UserDazhiListener',//大智金服
            'App\Listeners\Oneloan\Partner\UserRongdaiListener',//融贷
            'App\Listeners\Oneloan\Partner\UserYiyangListener',//意扬
            'App\Listeners\Oneloan\Partner\UserJiajiarongListener'//佳佳融
        ],

        //延迟推送
        'App\Events\Oneloan\Partner\UserSpreadBatchEvent' => [
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadDongfangBatchListener',     //东方金融
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadHeiniuBatchListener',       //黑牛保险
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadOxygendaiBatchListener',    //氧气贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadPaipaidaiBatchListener',    //拍拍贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadXinyiBatchListener',        //新一贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadZhudaiBatchListener',       //助贷网
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadHengchangBatchListener',    //恒昌金融
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadHoubenBatchListener',       //厚本金融
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadXiaoxiaoBatchListener',     //小小金融
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadZhongtengxinBatchListener', //中腾信
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadYouliBatchListener',        //有利
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadXiaoxiao2BatchListener',    //小小金融2新渠道
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadNiwodaiBatchListener',      //你我贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadMiaolaBatchListener',       //你我贷-秒啦
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadMiaodaiBatchListener',      //秒贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadGongyinyingBatchListener',  //工银英
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadRongshidaiBatchListener',   //融时代
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadHengyidaiBatchListener',    //恒昌金融线下恒易贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadHougedaiBatchListener',    //猴哥贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadChunyuBatchListener',         //春雨
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadRenxinyongBatchListener',    //任信用
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadZhijiechedaiBatchListener',    //智借车贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadFangjinsuoBatchListener',    //房金所
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadXiyiBatchListener',//西伊
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadCainiaoBatchListener',//财鸟
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadJibaodaiBatchListener',//吉宝贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadYoulinewBatchListener',//有利2
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadZhanyewangBatchListener',//展业王
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadDazhiBatchListener',//大智金服
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadRongdaiBatchListener',//融贷
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadYiyangBatchListener',//意扬
            'App\Listeners\Oneloan\Partner\Batch\UserSpreadJiajiarongBatchListener',//佳佳融
        ],

    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen('event.name', function ($key, $value) {
            //
        });
    }

}
