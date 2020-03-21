<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\HengChangConstant;
use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Events\Oneloan\Partner\UserSpreadEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Hengchang\HengchangConfig\HengchangConfig;
use App\Services\Core\Oneloan\Hengchang\HengchangService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Services\Core\Tools\JuHe\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * 北京恒昌利通投资管理有限公司
 * Class UserDongfangListener
 * @package App\Listeners\V1
 */
class UserHengChangListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_HENGCHANG_NID);

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
                    //logInfo('恒昌', ['data' => $spread]);
                    $this->pushHengChangData($spread);
                }
            }

        } catch (\Exception $exception) {
            logError('恒昌发送失败-catch');
            logError($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param $spread
     * @return bool
     */
    public function pushHengChangData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_HENGCHANG_NID;
        //身份证号判断
        if (!($this->isCertCard($spread))) {
            return true;
        }

        if ($spread['type_id'] == 0) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) {
                //年龄限制
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) {
                    //意向申请额度:1-20万
                    if ($this->checkMoney($spread['money'])) {
                        //条件限制
                        if ($this->checkCondition($spread)) {
                            //城市限制
                            $cityInfo = UserSpreadFactory::checkSpreadCity($spread);
                            $spread['cityCode'] = isset($cityInfo['city_code']) ? $cityInfo['city_code'] : '';
                            if ($spread['cityCode']) {
                                //判断延迟表中书否存在数据
                                $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                                if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                                {
                                    $this->waitPush($spread);
                                } elseif ($spread['batch_status'] == 0)  //立即推送
                                {
                                    //总量限制
                                    if ($spread['total'] < $spread['limit'] or 0 == $spread['limit']) //立即推送
                                    {
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
     * 立刻推送
     *
     * @param $data
     * @param $spread
     * @param $age
     */
    private function nowPush($spread)
    {
        //推送恒昌
        $res = HengchangService::register($spread);

        //处理结果
        //已存在流水表数据主键id
        $spread['id'] = $spread['spread_log_id'];
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['group_status'] = 0;
        $spread['request_status'] = 1;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;
        //logInfo('hengchang', ['res' => $res]);
        if (isset($res['ResponseCode'])) {
            $spread['message'] = HengchangConfig::getMessage($res['ResponseCode']);
            if ($res['ResponseCode'] == 0) {
                $spread['status'] = 1;
                $spread['group_status'] = 1;
                $spread['response_code'] = 1;
            } else {
                $spread['status'] = 0;
                $spread['group_status'] = 0;
                $spread['response_code'] = 2;
            }
        }

        // 创建赠险流水，如果流水存在但是状态为2，只更新不推送
        //是否是延迟推送流水  0不是，1是
        $spread['batch_status'] = 0;
        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            // 更新分发数据状态
            UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        }

        // 更新分组分发流水表
        if (isset($spread['group_id']) && !empty($spread['group_id'])) {
            UserSpreadFactory::insertOrUpdateUserSpreadGroupLog($spread);
        }

        // 更新推送次数等数据
        $spread['type_nid'] = SpreadNidConstant::SPREAD_HENGCHANG_NID;
        SpreadStrategy::updateSpreadCounts($spread);
    }

    /**
     * 延迟推送
     *
     *
     * @param $data
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

    /**
     * 判断年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if ($age >= 25 && $age <= 55) {
            return true;
        }

        return false;
    }

    /**
     * 意向申请额度:1-20万
     * @param string $money
     * @return bool
     */
    private function checkMoney($money = '')
    {
        $money = intval($money);
        if ($money >= 10000 && $money <= 200000) {
            return true;
        }

        return false;
    }

    /**
     * 判断用户身份条件
     * 1.月收入    大于3000
     * 2.工作类型   上班族（工作连续满6个月）
     * 3.信用记录    办理过信用卡或者银行贷款  =>   有信用卡，或车贷，房贷
     * 4.资产信息   公积金、社保、保单、有房任一资产信息，建议有公积金或者寿险保单人群为主
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        if (($data['occupation'] == '001' && $data['salary_extend'] == '001') or $data['occupation'] == '002' ) {
            if (in_array($data['salary'], ['002', '003', '004', '103', '104', '105', '106'])
                or $data['social_security'] == 1
                or $data['has_creditcard'] == 1) //条件限制
            {
                return true;
            }
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
        return $citys['city_code'];
    }

}
