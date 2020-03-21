<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataUserProductAccess;
use App\Models\Orm\DataUserProductAccessLog;
use App\Models\Orm\DataUserProductAccessText;


/**
 *
 * Class DataUserAccessFactory
 * @package App\Models\Factory\Api\Platform
 */
class DataUserAccessFactory extends AbsModelFactory
{
    /**
     * 存流水
     * @param array $params
     * @return bool
     */
    public static function insertDataUserAccessLog($params = [])
    {
        $log = new DataUserProductAccessLog();
        $log->user_id = $params['user_id'];
        $log->product_id = $params['product_id'];
        $log->product_name = $params['product_name'];
        $log->is_new_user = isset($params['is_new_user']) ? $params['is_new_user'] : 99;
        $log->qualify_status = isset($params['qualify_status']) ? $params['qualify_status'] : 99;
        $log->order_status = $params['order_status'];
        $log->blacklist_status = $params['blacklist_status'];
        $log->rejected_status = $params['rejected_status'];
        $log->overdue_status = $params['overdue_status'];
        $log->overdue_days = $params['overdue_days'];
        $log->response_text = $params['response_text'];
        $log->status = $params['status'];
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = $params['created_ip'];
        $log->updated_at = date('Y-m-d H:i:s', time());
        $log->updated_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 修改主表sd_data_user_access_progress数据
     * @param array $params
     * @return bool
     */
    public static function updateDataUserAccess($params = [])
    {
        $model = DataUserProductAccess::select()
            ->where(['user_id' => $params['user_id'], 'product_id' => $params['product_id']])
            ->first();

        if (empty($model)) {
            $model = new DataUserProductAccess();
            $model->user_id = $params['user_id'];
            $model->product_id = $params['product_id'];
            $model->created_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $params['created_ip'];
        }

        $model->product_name = $params['product_name'];
        $model->is_new_user = isset($params['is_new_user']) ? $params['is_new_user'] : 99;
        $model->qualify_status = isset($params['qualify_status']) ? $params['qualify_status'] : 99;
        $model->order_status = $params['order_status'];
        $model->blacklist_status = $params['blacklist_status'];
        $model->rejected_status = $params['rejected_status'];
        $model->overdue_status = $params['overdue_status'];
        $model->overdue_days = $params['overdue_days'];
        $model->status = $params['status'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        $model->save();
        return $model->id;
    }

    /**
     * 修改响应数据
     * @param array $params
     * @return bool
     */
    public static function updateDataUserAccessText($params = [])
    {
        $model = DataUserProductAccessText::select()
            ->where(['access_id' => $params['access_id']])
            ->first();

        if (empty($model)) {
            $model = new DataUserProductAccessText();
            $model->access_id = $params['access_id'];
            $model->created_at = date('Y-m-d H:i:s', time());
            $model->created_ip = $params['created_ip'];
        }

        $model->status = 1;
        $model->response_text = $params['response_text'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }
}