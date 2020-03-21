<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Niwodai\NiwodaiConfig\NiwodaiConfig;
use App\Services\Core\Oneloan\Niwodai\NiwodaiService;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Listeners\Oneloan\Partner\Callback\NiwodaiCallback;

/**
 * 你我贷延迟推送
 *
 * Class UserSpreadNiwodaiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadNiwodaiBatchListener extends AppListener
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
    public function handle(UserSpreadBatchEvent $event)
    {
        try {
            //logInfo('sss', ['data' => $event->data]);
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
                    //logInfo('东方延迟', ['data' => $batchData]);
                    $this->pushData($batchData);
                } else
                {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            logError('你我贷延迟推送失败-catch');
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
        if ($spread['type_nid'] == SpreadNidConstant::SPREAD_NIWODAI_NID) {
            $spread['age'] = Utils::getAge($spread['birthday']);
            //城市拼音
            $cityInfo = UserSpreadFactory::checkSpreadBatchCity($spread);
            $spread['cityname'] = isset($cityInfo['city_name']) ? $cityInfo['city_name'] : '';

            //推送数据
            $spreadParams = NiwodaiConfig::getParams($spread);
            NiwodaiService::apply($spreadParams,

                function($res) use ($spread) {
                    //处理结果
                    //是否是延迟推送流水  0不是，1是
                    $spread['batch_status'] = 1;
                    NiwodaiCallback::handleRes($res, $spread);

                    //更新spread
                    UserSpreadFactory::updateSpreadByMobile($spread['mobile']);
                    //更新发送状态
                    UserSpreadFactory::updateSpreadBatch($spread['batch_id']);

                }, function ($e){

                });
        }
    }

}
