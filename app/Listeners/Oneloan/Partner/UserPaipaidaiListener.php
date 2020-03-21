<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Paipaidai\PaipaidaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Listeners\Oneloan\Partner\Callback\PaipaidaiCallback;

class UserPaipaidaiListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_PAIPAIDAI_NID);
            if (!empty($type)) {

                //logInfo('event->data', ['data' => $event->data]);
                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);
                //数据统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status']) {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    //logInfo('拍拍贷', ['data' => $spread]);
                    $this->pushPaiPaiData($spread);
                }
            }
        } catch (\Exception $exception) {
            logError('拍拍贷发送失败-catch');
            logError($exception->getMessage());
        }
    }

    /**
     * 处理拍拍贷数据
     *
     * @param $spread
     * @return bool
     */
    public function pushPaiPaiData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_PAIPAIDAI_NID;
        //身份证号判断
        if (!($this->isCertCard($spread))) {
            return true;
        }

        //分发类型不存在
        if (0 == $spread['type_id']) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) //发放时间限制
        {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) //性别限制
            {
                //年龄限制
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) //年龄限制
                {
                    //判断延迟表中书否存在数据
                    $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                    if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                    {
                        $this->waitPush($spread);
                    } elseif ($spread['batch_status'] == 0)  //立即推送
                    {
                        // 若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
                        if (($spread['total'] < $spread['limit']) or ($spread['limit'] == 0)) {
                            $this->nowPush($spread);
                        }
                    }
                }
            }
        }
    }

    /**
     * 立即发送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        // 推送service
        PaipaidaiService::spread($spread,
            function($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                PaipaidaiCallback::handleRes($res, $spread);
            }, function ($e){

            });
    }

    /**
     * 延迟推送
     *
     * @param $spread
     */
    private function waitPush($spread)
    {
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }

    /**
     * 判断身份证号码是否存在
     *
     * @param $data
     * @return bool
     */
    private function isCertCard($data)
    {
        if (!empty($data['certificate_no'])) {
            return true;
        }

        return false;
    }
}
