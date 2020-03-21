<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Listeners\Oneloan\Partner\Callback\RongdaiCallback;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Rongdai\Rongdai\RongdaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
/**
 * 融贷
 * Class UserRongdaiListener
 * @package App\Listeners\V1
 */
class UserRongdaiListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_RONGDAI_NID);

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
            logError('融贷发送失败-catch');
            logError($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param $spread
     * @return bool
     */
    public function pushData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_RONGDAI_NID;
        //24小时限制
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }

        if (0 == $spread['type_id']) {
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
                    //自身条件限制
                    if ($this->checkCondition($spread)) {
                        //城市限制
                        $citys = UserSpreadFactory::checkSpreadCity($spread);
                        if (isset($citys['city_code']) && $citys['city_code']) {
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
     * 立刻推送
     *
     * @param $spread
     */
    private function nowPush($spread = [])
    {
        $spread['age'] = Utils::getAge($spread['birthday']);
        //  推送助贷网
        RongdaiService::spread($spread,
            function ($res) use ($spread) {
                //处理结果
                //是否是延迟推送流水  0不是，1是
                $spread['batch_status'] = 0;

                RongdaiCallback::handleRes($res, $spread);

            }, function ($e) {

            });

    }

    /**
     * 延迟推送
     * @param $spread
     */
    private function waitPush($spread)
    {
        // 创建推广流水
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }
    /**
     * 判断用户身份条件
     * 大于等于3万(1到3万当3万)    银行代发  有社保/有公积金/房产/车产/保单/微粒贷
     *
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        if ($data['money'] >= 10000 && in_array($data['salary_extend'], ['001'])) //大于等于3万
        {
            if ($data['social_security'] == 1
                or in_array($data['accumulation_fund'], ['001', '002'])
                or in_array($data['house_info'], ['001', '002'])
                or in_array($data['car_info'], ['001', '002'])
                or in_array($data['has_insurance'], ['1', '2'])
                or in_array($data['is_micro'], ['1'])
            ) {
                return true;
            }
        }

    }

    /**
     * 设置redis字符串的值和过期时间
     *
     * @param $key string 键
     * @param $value string 值
     * @param $outTime  int  时间（s）秒
     * @return mixed
     */
    public function setCache($key, $value, $outTime = null)
    {
        return Cache::put($key, $value, $outTime);
    }
}
