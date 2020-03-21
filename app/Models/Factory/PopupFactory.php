<?php

namespace App\Models\Factory;

use App\Helpers\Logger\SLogger;
use App\Models\AbsModelFactory;
use App\Models\Orm\Popup;
use App\Models\Orm\DataPopupApplyLog;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use Illuminate\Support\Facades\Log;

/**
 * Class PopupFactory
 * @package App\Models\Factory
 * 任务弹窗统计
 */
class PopupFactory extends AbsModelFactory
{
    /**获取弹窗信息
     * @param $id
     * @return array
     */
    public static function fetchPopupData($id)
    {
        $data = Popup::select(['id', 'name', 'description', 'status', 'is_new', 'url'])
            ->where(['id' => $id])
            ->get()->first();

        return $data ? $data->toArray() : [];
    }

    /**创建弹窗统计流水
     * @param $data
     * @param $description
     * @param $userId
     * @param $userArr
     * @param $deliveryId
     * @param $deliveryArr
     */
    public static function createPopupApplyLog($data, $description, $userId, $userArr, $deliveryId, $deliveryArr)
    {
        $log = new DataPopupApplyLog();
        $log->user_id = $userId;
        $log->username = $userArr['username'];
        $log->mobile = $userArr['mobile'];
        $log->device_id = $data['deviceId'];
        $log->click_source = $description['description'];
        $log->popup_id = $description['id'];
        $log->url = $description['url'] ?? "";
        $log->is_new = $description['is_new'];
        $log->device_id = $data['deviceId'];
        $log->channel_id = $deliveryId;
        $log->channel_title = $deliveryArr['title'];
        $log->channel_nid = $deliveryArr['nid'];
        $log->shadow_nid = $data['shadow_nid'] ? $data['shadow_nid'] : "sudaizhijia";
        $log->app_name = $data['app_name'] ? $data['app_name'] : "sudaizhijia";
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s');
        $log->created_ip = Utils::ipAddress();
        $log->save();
    }
}