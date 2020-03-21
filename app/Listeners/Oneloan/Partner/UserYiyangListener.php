<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Yiyang\YiyangService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Listeners\Oneloan\Partner\Callback\YiyangCallback;

/**
 * 意扬
 * Class UserZhongtengxinListener
 * @package App\Listeners\V1
 */
class UserYiyangListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_YIYANG_NID);
            if (!empty($type))
            {
                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);
                // 推广统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'])
                {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($spread);
                }
            }
        } catch (\Exception $exception) {
            logError('意扬发送失败-catch', $exception->getMessage());
        }
    }

    /**
     * 条件判断
     *
     * @param array $spread
     * @return bool
     */
    public function pushData($spread = [])
    {
        if ($spread['type_id'] == 0) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) {
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //年龄限制
                if (SpreadStrategy::checkSpreadAge($spread)) {
                    if(UserSpreadFactory::checkSpreadCity($spread)){
                        //判断延迟表中书否存在数据
                        $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                        if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                        {
                            $this->waitPush($spread);
                        } elseif ($spread['batch_status'] == 0)  //立即推送
                        {
                            //推送总量限制
                            if ($spread['total'] < $spread['limit'] or 0 == $spread['limit']) {
                                $this->nowPush($spread);
                            }
                        }

                    }
                }
            }
        }
    }

    /**
     * 立即发送
     *
     * @param array $spread
     */
    private function nowPush($spread = [])
    {
        // 推广service
        YiyangService::register($spread,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                YiyangCallback::handleRes($res, $spread);
            }, function ($e) {

            });

    }

    /**
     * 延迟推送
     *
     * @param array $spread
     */
    private function waitPush($spread = [])
    {
        // 延迟推送信息
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);
        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }



}
