<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserIdfaEvent;
use App\Helpers\Logger\SLogger;
use App\Services\Core\Idfa\IdfaService;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * IDFA激活回调事件
 *
 * Class UserIdfaListener
 * @package App\Listeners\V1
 */
class UserIdfaListener extends AppListener
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
     *
     *
     * @param UserIdfaEvent $event
     */
    public function handle(UserIdfaEvent $event)
    {
        //数据
        $data = $event->data;
        //请求开始
        logInfo('tick_idfa_start', ['data' => $data]);

        //IDFA激活回调事件
        try {

            //请求tick项目接口
            $res = IdfaService::i()->toIdfaService($data);

            logInfo('tick_idfa_ok_' . $data['idfaId'], ['data' => $res]);
            //返回失败
            if (!$res) {
                logError('idfa激活回调-try');
                logInfo('tick_idfa_error_' . $data['idfaId'], ['data' => $res]);
            }

        } catch (\Exception $e) {

            logError('idfa激活回调-catch');
            logError($e->getMessage());
        }
    }

}
