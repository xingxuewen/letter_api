<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Constants\XinyidaiConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Houbenjinrong\HoubenjinrongConfig\HoubenjinrongConfig;
use App\Services\Core\Oneloan\Houbenjinrong\HoubenjinrongService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Utils;
use App\Listeners\Oneloan\Partner\Callback\HoubenCallback;

/**
 * 厚本金融
 *
 * Class UserHoubenListener
 * @package App\Listeners\V1
 */
class UserHoubenListener extends AppListener
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
        try{
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_HOUBEN_NID);
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
                    //logInfo('厚本',['data'=>$spread]);
                    $this->pushHoubenData($spread);
                }
            }
        }catch (\Exception $exception) {
            logError('厚本金融发送失败-catch', $exception->getMessage());
        }
    }

    /**
     * 处理厚本数据
     *
     * @param $spread
     * @return bool
     */
    public function pushHoubenData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_HOUBEN_NID;

        //分发类型不存在
        if (0 == $spread['type_id']) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread))
        {
            //性别限制
            if(SpreadStrategy::checkSpreadSex($spread))
            {
                //获取城市和获取城市编码
                $spread['birthday'] = DateUtils::getBirthday($spread['birthday']);
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //信用卡信息
                $spread['hasCreditCard'] = $spread['has_creditcard'];
                //城市编码
                $cityInfo = UserSpreadFactory::checkSpreadCity($spread);
                $spread['city_code'] = isset($cityInfo['city_code']) ? $cityInfo['city_code'] : '';
                //年龄
                if (SpreadStrategy::checkSpreadAge($spread))
                {
                    //借款金额2w以上
                    if ($this->checkMoney($spread['money']))
                    {
                        //用户条件
                        if ($this->checkCondition($spread))
                        {
                            //筛选城市
                            if ($spread['city_code'] > 0)
                            {
                                //判断延迟表中书否存在数据
                                $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                                if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                                {
                                    $this->waitPush($spread);
                                } elseif ($spread['batch_status'] == 0)  //立即推送
                                {
                                    //推广总量限制
                                    if ($spread['total'] < $spread['limit'] or 0 == $spread['limit'])
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

    /*
     * 立即推送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        //  推送service
        $spreadParams = HoubenjinrongConfig::getParams($spread);
        HoubenjinrongService::push($spreadParams,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;
                HoubenCallback::handleRes($res, $spread);
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
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }

    /**
     * 借款金额2w以上
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
     * 有信用卡
     * @param $hasCreditcard
     * @return bool
     */
    private function checkHasCreditcard($hasCreditcard)
    {
        if ($hasCreditcard == 1) {
            return true;
        }

        return false;
    }

    /**
     * 有房贷推送
     * @param string $house
     * @return bool
     */
    private function checkHouse($house = '')
    {
        if ($house == '001') {
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
     * 判断用户身份条件
     *  工作：上班（12个月以、一年以上），私营业主（一年以上）
     * 月入：2千-5千、5千-1万、1万以上
     * 资产：信用卡有  房贷 车贷
     * 房公积金寿险保单3有其一
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        if (in_array($data['salary'], ['002', '003', '004', '103', '104', '105', '106'])) //1.月收入
        {
            if ( (in_array($data['occupation'], ['001', '002']) && in_array($data['work_hours'], ['002', '003']))
                || ('003' == $data['occupation'] && in_array($data['business_licence'], ['001', '002'])) ) //2.工作类型
            {
                if (1 == $data['has_creditcard'] or in_array($data['house_info'], ['001', '002']) or in_array($data['car_info'], ['001', '002'])) //3.信用记录
                {
                    if ($data['has_insurance'] > 0 or in_array($data['accumulation_fund'], ['001', '002']) ) //4.资产信息
                    {
                        return true;
                    }
                }
            }
        }


        return false;
    }

    /**
     * 判断城市
     * @param array $data
     * @return int
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

    /**
     * 重新获取城市判断
     *
     * @param $mobile
     * @return int
     */
    private function checkCityAgain($mobile)
    {
        $city = '';
        $arrCity = XinyidaiConstant::PUSH_A_SORT_CITY;
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        if (!empty($phoneInfo)) {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            if (empty($city)) {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            }
        }

        foreach ($arrCity as $key => $code) {
            if (strpos($city, $key) !== false) {
                return $code;
            }
        }

        return -1;
    }
}
