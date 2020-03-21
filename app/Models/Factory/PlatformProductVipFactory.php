<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProductVip;

class PlatformProductVipFactory extends AbsModelFactory
{
    public static function getAll(array $params = [])
    {
        $query = new PlatformProductVip();

        if (!empty($params['select'])) {
            $query = $query->select($params['select']);
        }

        if (!empty($params['where'])) {
            $query = $query->where($params['where']);
        }

        if (!empty($params['where_in'])) {
            foreach ($params['where_in'] as $in_k => $in_v) {
                $query = $query->whereIn($in_k, $in_v);
            }
        }

        if (!empty($params['where_not_in'])) {
            foreach ($params['where_not_in'] as $not_in_k => $not_in_v) {
                $query = $query->whereNotIn($not_in_k, $not_in_v);
            }
        }

        if (!empty($params['or'])) {
            $or = $params['or'];
            $query = $query->where(function ($query) use ($or) {
                foreach ($or as $item) {
                    $query = $query->orWhere($item[0], $item[1], $item[2]);
                }
            });
        }

        if (!empty($params['order'])) {
            foreach ($params['order'] as $order_k => $order_v) {
                $query = $query->orderBy($order_k, $order_v);
            }
        }

        return $query->get()->toArray();
    }

    public static function insertGetId(array $insertData)
    {
        return PlatformProductVip::insertGetId($insertData);
    }

    public static function update(array $where, array $set_data)
    {
        $query = new PlatformProductVip();

        return $query->where($where)->update($set_data);
    }
}
