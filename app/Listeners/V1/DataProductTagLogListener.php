<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Factory\UserFactory;
use App\Models\Orm\DataProductTagLog;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;

/**
 * 添加标签规则产品流水监听
 * Class UserRegCountListener
 * @package App\Listeners\V1
 */
class DataProductTagLogListener extends AppListener
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
     * Handle the event.
     *
     * @param  AppEvent $event
     * @return void
     */
    public function handle(AppEvent $event)
    {
        $data = $event->data;
        DB::beginTransaction();
        try {
            //用户信息
            $user = UserFactory::fetchUserNameAndMobile($data['userId']);
            //渠道信息
            $deliveryId = DeliveryFactory::fetchDeliveryId($data['userId']);
            //获取渠道信息
            $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
            //马甲包唯一标识
//            $shadowId = ShadowFactory::getShadowId($data['userId']);
//            $shadowNid = ShadowFactory::getShadowNid($shadowId);
            $shadowNid = $data['shadow_nid'];


            $list = $data['list'];
            foreach ($list as $key => $val) //遍历
            {
                $log = new DataProductTagLog();
                $log->user_id = $data['userId'];
                $log->username = $user['username'];
                $log->mobile = $user['mobile'];
                $log->platform_id = $val['platform_id'];
                $log->platform_product_id = $val['platform_product_id'];
                $log->platform_product_name = $val['platform_product_name'];
                $log->channel_id = $deliveryArr['id'];
                $log->channel_title = $deliveryArr['title'];
                $log->channel_nid = $deliveryArr['nid'];
                $log->shadow_nid = isset($shadowNid) ? $shadowNid : 'sudaizhijia';
                $log->from = isset($data['from']) ? $data['from'] : 0;
                $log->user_agent = UserAgent::i()->getUserAgent();
                $log->created_at = date('Y-m-d H:i:s', time());
                $log->created_ip = Utils::ipAddress();

                $log->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            logError('注册渠道统计失败-catch', $e->getMessage());
        }

        return false;
    }

}
