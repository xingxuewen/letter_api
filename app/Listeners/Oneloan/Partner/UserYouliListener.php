<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Youli\YouliConfig\YouliConfig;
use App\Services\Core\Oneloan\Youli\YouliService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Strategies\SpreadStrategy;
use function Couchbase\defaultDecoder;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Listeners\Oneloan\Partner\Callback\YouliCallback;

/**
 * 有利
 * Class UserYouliListener
 * @package App\Listeners\V1
 */
class UserYouliListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_YOULI_NID);

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
                    //logInfo('有利', ['data' => $spread]);
                    $this->pushYouliData($spread);
                }
            }
        } catch (\Exception $exception) {
            logError('有利保险发送失败-catch');
            logError($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param $spread
     * @return bool
     */
    public function pushYouliData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_YOULI_NID;
        //24小时限制
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }
        //身份证号判断
        if (!($this->isCertCard($spread))) {
            return true;
        }
        //分发类型不存在
        if (0 == $spread['type_id']) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) {
                //年龄限制
                $idcardInfo = Utils::getAgeAndBirthDayByCard($spread['certificate_no']);
                $age = Utils::getAge($idcardInfo['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) {

                    //借款金额1万以上
                    if ($this->checkMoney($spread['money'])) {
                        //城市
                        if ($this->checkCityAgain($spread)) {

                            //判断延迟表中书否存在数据
                            $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                            if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                            {
                                $this->waitPush($spread);
                            } elseif ($spread['batch_status'] == 0)  //立即推送
                            {
                                //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
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
     * 立刻推送
     *
     * @param $spread
     */
    private function nowPush($spread = [])
    {
        //推送有利保险
        $spreadParams = YouliConfig::getParams($spread);
        YouliService::register($spreadParams,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                YouliCallback::handleRes($res, $spread);
            }, function ($e) {

            });
    }

    /**
     * 延迟推送
     * @param $spread
     */
    private function waitPush($spread)
    {
        // 创建流水
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
        return true;

//        if ($age >= 25 && $age <= 55) {
//            return true;
//        }
//
//        return false;
    }

    /**
     * 借款金额5w以上
     * @param $money
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
     * 判断用户身份条件
     *
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        //有寿险保单、有车、有房选其一　　　　　　
        if ($data['has_insurance'] > 0 or in_array($data['car_info'], ['001', '002']) or in_array($data['house_info'], ['001', '002'])) {
            return true;
        }

        return false;
    }

    /**
     * 手机号定位城市
     * @param array $data
     * @return array|bool
     */
    private function checkCityAgain($data = [])
    {
        //  河北 北京 20180621
        $provinceArr = ['河北', '北京'];
        $phoneInfo = JuhePhoneService::getPhoneInfo($data['mobile']);

        if (!empty($phoneInfo)) {
            //手机号定位城市没有'市'字
            $province = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            if (in_array($province, $provinceArr)) {
                return true;
            }
        }

        return false;
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
