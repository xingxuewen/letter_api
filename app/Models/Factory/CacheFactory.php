<?php

namespace App\Models\Factory;

use App\Constants\SpreadConstant;
use App\Models\AbsModelFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class CacheFactory
 * @package App\Models\Factory
 * Cache
 */
class CacheFactory extends AbsModelFactory
{

    // cache存储  7天
    public static function putValueToCache($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->second(7 * 24 * 3600));
    }

    //永久存储
    public static function putValueToCacheForever($key, $value)
    {
        return Cache::forever($key, $value, Carbon::now());
    }

    //存储7200秒
    public static function putValueToCacheTwoMinutes($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->second(7200));
    }

    // 存储10分钟
    public static function putValueToCacheToOneloan($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->second(10 * 60));
    }

    // 从cache 读取数据
    public static function getValueFromCache($key)
    {
        return Cache::get($key);
    }

    // cache 中数据是否存在
    public static function existValueFromCache($key)
    {
        return Cache::get($key) ? true : false;
    }

    //自增
    public static function incrementToCache($key, $value = 1)
    {
        return Cache::increment($key, $value);
    }

    // 一键贷自增
    public static function incrementCacheToOneloan($spread = [])
    {
        $key = SpreadConstant::SPREAD_QUOTA . $spread['group_type_nid'] . '_' . $spread['mobile'];
        return Cache::increment($key, 1);
    }

    /**
     * redis中存储用户点击立即申请产品ids
     *
     * @param string $userId
     * @return array
     */
    public static function fetchRedisProductIds($userId = '')
    {
        $key = 'sd_product_apply_' . $userId;
        $data = Cache::get($key);
        return $data ? json_decode($data, true) : [];
    }

    /**
     * 取出存在于redis中的产品ids
     * 用户id唯一、30天有效期
     *
     * @param array $datas
     * @return mixed
     */
    public static function putProductIdToCache($datas = [])
    {
        $key = 'sd_product_apply_' . $datas['userId'];
        $productIds = Cache::get($key) ? json_decode(Cache::get($key), true) : [];

        if (!is_array($productIds)) $productIds = [];
        if (!in_array($datas['productId'], $productIds)) array_push($productIds, $datas['productId']);
        //转化为json
        $value = json_encode($productIds);
        //存入redis中，有效期30天
        return Cache::put($key, $value, Carbon::now()->second(30 * 24 * 3600));
    }

    /**
     * 根据key值取出redis中的产品，并转化为array格式
     *
     * @param $key
     * @return array|mixed
     */
    public static function fetchRedisProductValueIdsByKey($key)
    {
        return Cache::get($key) ? json_decode(Cache::get($key), true) : [];
    }

    /**
     * 追加到原redis的key中
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function putProductValueIdsToCache($key, $value)
    {
        return Cache::put($key, json_encode($value), Carbon::now()->second(7200));
    }

    /**
     * 清楚特定key的缓存值
     *
     * @param $key
     * @return mixed
     */
    public static function delProductValueIds($key)
    {
        return Cache::forget($key);
    }
}
