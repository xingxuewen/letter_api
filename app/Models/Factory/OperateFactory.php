<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\ProductOperateConfig;

/**
 * 运营工厂
 *
 * Class OperateFactory
 * @package App\Models\Factory
 */
class OperateFactory extends AbsModelFactory
{

    /**
     * 产品运营配置值
     *
     * @param string $nid
     * @return array
     */
    public static function fetchProductOperateConfigByNid($nid = '')
    {
        $value = ProductOperateConfig::select(['value','logo'])
            ->where(['nid' => $nid, 'status' => 1])
            ->first();

        return $value ? $value->toArray() : [];
    }

    /**
     * 产品运营配置值
     * @param string $nid
     * @return array
     */
    public static function fetchGuidesConfigByNid($nid = '')
    {
        $value = ProductOperateConfig::select(['status'])
            ->where(['nid' => $nid])
            ->first();

        return $value ? $value->toArray() : [];
    }
}