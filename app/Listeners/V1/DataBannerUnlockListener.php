<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserUnlockLoginEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\DataProductExposureFactory;
use App\Models\Factory\DeliveryFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * 连登点击统计
 *
 * Class UserUnlockLoginListener
 * @package App\Listeners\V1
 */
class DataBannerUnlockListener extends AppListener
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    /**
     * @param UserUnlockLoginEvent $event
     */
    public function handle(AppEvent $event)
    {
        try {
            $data = $event->data;
            $bannerId = isset($data['unlockLoginId']) ? $data['unlockLoginId'] : '';

            //查询当前解锁连登广告信息
            $data['bannerUnlock'] = BannersFactory::fetchBannerUnlockLoginById($bannerId);
            //获取渠道id
            $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
            //获取渠道信息
            $data['deliverys'] = DeliveryFactory::fetchDeliveryArray($deliveryId);
            //统计流水
            $log = BannersFactory::createBannerUnlockLoginLog($data);
            if (!$log) {
                logError('连登点击统计-try', $data);
            }

        } catch (\Exception $e) {

            logError('连登点击统计-catch', $e->getMessage());
        }

    }

}
