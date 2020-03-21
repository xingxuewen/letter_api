<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserUnlockLoginEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\DataProductExposureFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * 产品曝光统计事件
 *
 * Class UserUnlockLoginListener
 * @package App\Listeners\V1
 */
class DataProductExposureListener extends AppListener
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

            $datas['user_id'] = $data['userId'];
            $datas['device_id'] = $data['deviceNum'];
            $datas['product_list'] = $data['exposureProIds'];
            $res = DataProductExposureFactory::AddExposure($datas);
            if (!$res) {
                logError('产品曝光统计失败-try', $datas);
            }

        } catch (\Exception $e) {

            logError('产品曝光统计失败-catch', $e->getMessage());
        }

    }

}
