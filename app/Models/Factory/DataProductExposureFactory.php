<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataProductExposure;

class DataProductExposureFactory extends AbsModelFactory
{

    /** 曝光度统计
     * @param array $data
     * @return mixed
     */
    public static function AddExposure($params)
    {
        //添加
        $exposureObj = new DataProductExposure();
        $exposureObj->user_id = $params['user_id'];
        $exposureObj->device_id = $params['device_id'];
        $exposureObj->product_list = $params['product_list'];
        $exposureObj->user_agent = UserAgent::i()->getUserAgent();
        $exposureObj->created_ip = Utils::ipAddress();
        $exposureObj->created_at = date('Y-m-d H:i:s', time());
        return $exposureObj->save();
    }
}
