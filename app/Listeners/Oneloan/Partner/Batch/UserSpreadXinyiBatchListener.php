<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\SpreadNidConstant;
use App\Constants\XinyidaiConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Xinyidai\XinyidaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Listeners\Oneloan\Partner\Callback\XinyidaiCallback;

/**
 * 新一贷延迟推送
 *
 * Class UserSpreadXinyiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadXinyiBatchListener extends AppListener
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
        try{
            //推送数据
            $batchData = $event->data;
            //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
            if ($batchData['total'] < $batchData['limit'] or 0 == $batchData['limit'])
            {
                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($batchData);
                //没有流水推送，状态为2推送，3延迟推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'] || 3 == $spreadLogInfo['status'])
                {
                    $spread['id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($batchData);
                } else {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        }catch (\Exception $exception){
            logError('新一贷延迟推送失败-catch');
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
        if($spread['type_nid'] == SpreadNidConstant::SPREAD_XINYIDAI_NID)
        {
            $spread['age'] = Utils::getAge($spread['birthday']);
            //城市编码
            $cityInfo = UserSpreadFactory::checkSpreadCity($spread);
            $spread['city_code'] = isset($cityInfo['city_code']) ? $cityInfo['city_code'] : '';
            //推送service
            XinyidaiService::spread($spread,
                function($res) use ($spread) {
                    //处理结果
                    //是否是延迟推送【0立即推送，1延迟推送】
                    $spread['batch_status'] = 1;
                    $spread = XinyidaiCallback::handleRes($res, $spread);

                    //更新spread
                    UserSpreadFactory::updateSpreadByMobile($spread['mobile']);
                    //更新发送状态
                    UserSpreadFactory::updateSpreadBatch($spread['batch_id']);

                }, function ($e){

                });
        }
    }

    /**
     * 判断城市
     *
     * @param array $data
     * @return int
     */
    private function checkCity($data = [])
    {
        $city = isset($data['city']) ? $data['city'] : '';
        $city = mb_substr($city, 0, -1);

        //新一贷符合的A类城市列表
        $newLoanCitys = XinyidaiConstant::PUSH_A_SORT_CITY;

        if (array_key_exists($city, $newLoanCitys)) {
            return $newLoanCitys[$city];
        }

        return -1;
    }

    /**
     * 后台配置城市
     *
     * @param array $data
     * @return bool
     */
    private function reCheckCity($data = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //有城市限制
        if (empty($citys)) {
            return false;
        }

        return $citys['city_code'];
    }


}
