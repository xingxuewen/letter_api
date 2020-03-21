<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Listeners\Oneloan\Partner\Callback\CainiaoCallback;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Cainiao\CainiaoService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\Log;

/**
 * 财鸟
 * Class UserChunyuListener
 * @package App\Listeners\V1
 */
class UserCainiaoListener extends AppListener
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
     * @param  Event $event
     * @return void
     */
    public function handle(AppEvent $event)
    {

        try {
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_CAINIAO_NID);

            if (!empty($type)) {
                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);

                // 推广统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status']) {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($spread);
                }
            }
        } catch (\Exception $exception) {
            logError('财鸟发送失败-catch');
            logError($exception->getMessage());
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
                    //贷款额度限制
                    if ($this->checkMoney($spread['money'])) {
                        //个人条件限制
                        if ($this->checkCondition($spread)) {
                            //城市限制
                            if (UserSpreadFactory::checkSpreadCity($spread)) {
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
        CainiaoService::register($spread,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                CainiaoCallback::handleRes($res, $spread);
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

    /**
     * 判断年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if ($age >= 23 && $age <= 55) {
            return true;
        }

        return false;
    }

    /**
     * 判断用户身份条件
     *
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        //微粒贷、公积金、社保三选一
        //且银行代发　　　　　　　
        if ((in_array($data['accumulation_fund'], ['001', '002']) || $data['social_security'] > 0 || $data['is_micro'] > 0) && $data['salary_extend'] == '001') {
            return true;
        }

        return false;
    }
    /**
     * 金额限制
     *
     * @param $money(大于5万 1到5万按5万算)
     * @return bool
     */
    private function checkMoney($money)
    {
        if ($money >= 10000) {
            return true;
        }

        return false;
    }

    /**
     * 数据库存储城市是否符合规则
     *
     * @param array $data
     * @return bool
     */
    private function checkCityBySql($data = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //有城市限制
        if (empty($citys)) {
            return false;
        }
        //超过城市限制条数
        if ($citys['today_limit'] > 0 && $citys['today_limit'] <= $citys['today_total']) {
            return false;
        }
        return true;
    }

}
