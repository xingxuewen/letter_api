<?php

namespace App\Models\Factory;


use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserPromotionsFail;
use App\Models\Orm\UserPromotionsLog;

class UserPromoteFactory extends AbsModelFactory
{
    /**
     * 检查是否有存在
     *
     * @param $params
     * @return bool
     */
    public static function checkIsPromote($params)
    {
        $log = UserPromotionsLog::where(['mobile' => $params['mobile'], 'promotions_nid' => $params['promotions_nid']])->first();

        return $log ? true : false;
    }

    /**
     * 更新分发数据用户
     * @param array $datas
     * @return bool
     */
    public static function createOrUpdateUserPromote($datas = [])
    {
        $model = UserPromotionsLog::where('mobile', $datas['mobile'])->where('promotions_nid', $datas['promotions_nid'])->first();
        if (empty($model)) {
            $model = new UserPromotionsLog();
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : '0';
            $model->mobile = isset($datas['mobile']) ? $datas['mobile'] : '0';
            $model->name = isset($datas['name']) ? $datas['name'] : '';
            $model->certificate_no = isset($datas['id_card_number']) ? $datas['id_card_number'] : '';
            $model->promotions_nid = isset($datas['promotions_nid']) ? $datas['promotions_nid'] : 'promotions_nid';
            $model->channel_id = isset($datas['channel_id']) ? $datas['channel_id'] : '';
            $model->channel_title = isset($datas['channel_title']) ? $datas['channel_title'] : '';
            $model->channel_nid = isset($datas['channel_nid']) ? $datas['channel_nid'] : '';
            $model->order_num = isset($datas['order_num']) ? $datas['order_num'] : '';
            $model->status = isset($datas['status']) ? $datas['status'] : 2;
            $model->result = isset($datas['result']) ? $datas['result'] : '';
            $model->message = isset($datas['message']) ? $datas['message'] : '';
            $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $model->updated_ip = isset($datas['created_ip']) ? $datas['created_ip'] : Utils::ipAddress();
        } else {
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : $model->user_id;
            $model->name = isset($datas['name']) ? $datas['name'] : $model->name;
            $model->certificate_no = isset($datas['id_card_number']) ? $datas['id_card_number'] : $model->certificate_no;
            $model->promotions_nid = isset($datas['promotions_nid']) ? $datas['promotions_nid'] : $model->promotions_nid;
            $model->channel_id = isset($datas['channel_id']) ? $datas['channel_id'] : $model->channel_id;
            $model->channel_title = isset($datas['channel_title']) ? $datas['channel_title'] : $model->channel_title;
            $model->channel_nid = isset($datas['channel_nid']) ? $datas['channel_nid'] : $model->channel_nid;
            $model->order_num = isset($datas['order_num']) ? $datas['order_num'] : $model->order_num;
            $model->status = isset($datas['status']) ? $datas['status'] : $model->status;
            $model->result = isset($datas['result']) ? $datas['result'] : $model->result;
            $model->message = isset($datas['message']) ? $datas['message'] : $model->message;
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
        }

        $model->save();

        return $model->id;
    }

    public static  function CreateOrUpdatePromotionFail($datas = [])
    {
        $model = UserPromotionsFail::where('mobile', $datas['mobile'])->where('promotions_nid', $datas['promotions_nid'])->first();
        if (empty($model)) {
            $model = new UserPromotionsFail();
            $model->promotions_log_id = isset($datas['promotion_id']) ? $datas['promotion_id'] : '';
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : '0';
            $model->mobile = isset($datas['mobile']) ? $datas['mobile'] : '0';
            $model->name = isset($datas['name']) ? $datas['name'] : '';
            $model->certificate_no = isset($datas['id_card_number']) ? $datas['id_card_number'] : '';
            $model->promotions_nid = isset($datas['promotions_nid']) ? $datas['promotions_nid'] : 'promotions_nid';
            $model->channel_id = isset($datas['channel_id']) ? $datas['channel_id'] : '';
            $model->channel_title = isset($datas['channel_title']) ? $datas['channel_title'] : '';
            $model->channel_nid = isset($datas['channel_nid']) ? $datas['channel_nid'] : '';
            $model->status = isset($datas['status']) ? $datas['status'] : 0;
            $model->created_at = $model->updated_at = date('Y-m-d H:i:s', time());
        } else {
            $model->promotions_log_id = isset($datas['promotion_id']) ? intval($datas['promotion_id']) : $model->promotions_log_id;
            $model->user_id = isset($datas['user_id']) ? intval($datas['user_id']) : $model->user_id;
            $model->name = isset($datas['name']) ? $datas['name'] : $model->name;
            $model->certificate_no = isset($datas['id_card_number']) ? $datas['id_card_number'] : $model->certificate_no;
            $model->promotions_nid = isset($datas['promotions_nid']) ? $datas['promotions_nid'] : $model->promotions_nid;
            $model->channel_id = isset($datas['channel_id']) ? $datas['channel_id'] : $model->channel_id;
            $model->channel_title = isset($datas['channel_title']) ? $datas['channel_title'] : $model->channel_title;
            $model->channel_nid = isset($datas['channel_nid']) ? $datas['channel_nid'] : $model->channel_nid;
            $model->order_num = isset($datas['order_num']) ? $datas['order_num'] : $model->order_num;
            $model->status = isset($datas['status']) ? $datas['status'] : $model->status;
            $model->updated_at = date('Y-m-d H:i:s', time());
        }

        $model->save();

        return $model->id;
    }
}