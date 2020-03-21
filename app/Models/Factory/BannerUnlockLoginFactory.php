<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\BannerUnlockLogin;

class BannerUnlockLoginFactory extends AbsModelFactory
{
    /**
     * 获取解锁的id
     * @param array $params
     * @return array
     */
    public static function fetchUnlockIdByUnlockDay($params = [])
    {
        $data = BannerUnlockLogin::where('unlock_day', '<=', $params['login_count'])->whereIn('nid', $params['unlock_nid'])->where('status', 1)->pluck('id');

        return $data ? $data->toArray() : [];
    }

    /**
     * 获取全部解锁id
     * @param array $params
     * @return array
     */
    public static function fetchUnlockIds($params = [])
    {
        $data = BannerUnlockLogin::whereIn('nid', $params['unlock_nid'])->where('status', 1)->pluck('id');

        return $data ? $data->toArray() : [];
    }

    public static function getAll(array $params = [])
    {
        $query = new BannerUnlockLogin();

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
}
