<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\DataIprefuse;

class DataIprefuseFactory extends AbsModelFactory
{
    /**
     * 获取当前ip信息
     * @param $ip
     * @return array
     */
    public static function fetchIpInfo($ip)
    {
        $ipInfo = DataIprefuse::where('ipaddr', $ip)->first();

        return $ipInfo ? $ipInfo->toArray() : [];
    }

    /**
     * 存储ip信息
     * @param $params
     */
    public static function insertDataIprefuse($params)
    {
        $ip = new DataIprefuse();
        $ip->ipaddr = $params['ipaddr'];
        $ip->addr = $params['addr'];
        $ip->addtime = date('Y-m-d H:i:s');
        return $ip->save();
    }
}
