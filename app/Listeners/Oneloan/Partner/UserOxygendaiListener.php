<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\OxygendaiConstant;
use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Oxygendai\OxygendaiService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Utils;
use Log;
use App\Listeners\Oneloan\Partner\Callback\OxygendaiCallback;

class UserOxygendaiListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_OXYGENDAI_NID);
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
                    //logInfo('氧气贷', ['data' => $spread]);
                    $this->pushOxygendaiData($spread);
                }
            }
        } catch (\Exception $exception) {
            logError('氧气贷发送失败-catch', $exception->getMessage());
        }
    }

    /**
     * 处理氧气贷数据
     *
     * @param $spread
     * @return bool
     */
    public function pushOxygendaiData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_OXYGENDAI_NID;
        //24小时之前
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }

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
                //获取城市和获取城市编码
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //$spread['hasCreditCard'] = $spread['has_creditcard'];
                //年龄限制
                if (SpreadStrategy::checkSpreadAge($spread)) //年龄限制
                {
                    //用户条件
                    if ($this->checkCondition($spread)) {
                        //筛选城市
                        if (UserSpreadFactory::checkSpreadCity($spread)) {
                            //判断延迟表中书否存在数据
                            $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                            if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                            {
                                $this->waitPush($spread);
                            } elseif ($spread['batch_status'] == 0)  //立即推送
                            {
                                //推广总量限制
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

    /**
     * 立即发送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        //使用批量接口
        $spreads = array($spread);
        OxygendaiService::spreadList($spreads,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                OxygendaiCallback::handleRes($res, $spread);
            }, function ($e) {

            });
    }

    /**
     * 延迟推送
     *
     * @param $spread
     */
    private function waitPush($spread)
    {
        // 创建流水
//        $spread['type_id'] = $data['type_id'];
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
     * 匹配条件
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        //有信用卡
        if ($data['has_creditcard'] == 1)//有信用卡
        {
            //房产　　　　　　　　　
            if (in_array($data['house_info'], ['001', '002']) or $data['has_insurance'] == 2)//房产
            {
                return true;
            }

        }

        return false;
    }

    /**
     * 重新获取城市判断
     *
     * @param $mobile
     * @return bool
     */
    private function checkCityAgain($mobile)
    {
        $city = '';
        $arrCity = OxygendaiConstant::PUSH_CITY;
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);

        if (!empty($phoneInfo)) {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';

            if (empty($city)) {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';

            }
        }

        if (in_array($city, $arrCity)) {
            return true;
        }
        return false;
    }

    /**
     * 检查城市
     * @param array $data
     * @return bool
     */
    private function checkCity($data = [])
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
