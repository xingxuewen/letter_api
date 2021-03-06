<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Helpers\Logger\SLogger;
use App\Listeners\Oneloan\Partner\Callback\ZhijiechedaiCallback;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Zhijiechedai\ZhijiechedaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * 智借车贷延迟推送
 *
 * Class UserSpreadZhijiechedaiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadZhijiechedaiBatchListener extends AppListener
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
        try {
            //推送数据
            $batchData = $event->data;
            //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
            if ($batchData['total'] < $batchData['limit'] or 0 == $batchData['limit']) {
                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($batchData);
                //没有流水推送，状态为2推送,3延迟推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'] || 3 == $spreadLogInfo['status'])
                {
                    $batchData['id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($batchData);
                } else
                {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            logError('智借车贷延迟推送失败-catch');
            logError($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param array $spread spread表里的信息
     */
    public function pushData($spread)
    {
        $spread_nid = SpreadStrategy::getSpreadNid(SpreadNidConstant::SPREAD_ZHIJIECHEDAI_NID);
        if ($spread['spread_nid'] == $spread_nid) {
            $spread['age'] = Utils::getAge($spread['birthday']);
            //推送数据
            ZhijiechedaiService::spread($spread,
                function($res) use ($spread) {
                    //是否是延迟推送【0立即推送，1延迟推送】
                    $spread['batch_status'] = 1;
                    $spread = ZhijiechedaiCallback::handleRes($res, $spread);
                    //更新spread
                    UserSpreadFactory::updateSpreadByMobile($spread['mobile']);
                    //更新发送状态
                    UserSpreadFactory::updateSpreadBatch($spread['batch_id']);
                },function ($e){
                });
        }
    }

}
