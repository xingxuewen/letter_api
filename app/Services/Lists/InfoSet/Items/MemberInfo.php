<?php
namespace App\Services\Lists\InfoSet\Items;

use App\Models\Factory\ProductFactory;
use App\Models\Factory\DeliveryFactory;
use App\Services\Lists\InfoSet\InfoSetAbstract;
use App\Models\Factory\UserVipFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\CacheFactory;

class MemberInfo extends InfoSetAbstract
{
    /**
     * 获取用户信息
     *
     * @param userInfo
     * @return array
     */
    public static function get($userId)
    {
        return UserFactory::fetchUserById($userId);
    }

    /**
     * 是否会员 0不是会员 1是会员
     *
     * @param $userId
     * @return int
     */
    public static function isVip($userId)
    {
        return UserVipFactory::checkIsVip(array('userId'=>$userId));
    }

    /**
     * 是否新用户 0不是新用户 1是新用户
     *
     * @param $userId
     * @return int
     */
    public static function isNew($userId)
    {
        $user = UserFactory::fetchUserIsNew($userId);

        return empty($user) ? 0 : 1;
    }

    /**
     * 获取用户连登天数
     *
     * @param $userId
     * @return int
     */
    public static function getUserLoginDays($userId)
    {
        return UserFactory::fetchUserUnlockNumById($userId);
    }

    /**
     * 获取用户地域
     *
     * @param $userId
     * @return int
     */
    public static function getUserLocationId($userId)
    {
        $devices = DeviceFactory::fetchDevicesByUserId($userId);

        if (!empty($devices)) {
            return $devices['area_id'];
        } else {
            return 0;
        }
    }

    /**
     * 获取用户渠道id
     *
     * @param $userId
     * @return int
     */
    public static function getUserDeliveryId($userId)
    {
        return DeliveryFactory::fetchDeliveryId($userId);
    }

    /**
     * 获取用户不想看的产品
     * @return array
     */
    public static function getUserProductBlack($userId)
    {
        return ProductFactory::fetchBlackIdsByUserId(['userId'=>$userId]);
    }

    /**
     * 获取用户点击过立即申请产品ids
     * @return array
     */
    public static function fetchUserClickedProductIds($userId)
    {
        return CacheFactory::fetchRedisProductIds($userId);
    }

    /**
     * 得到用户第一次下载记录的时间戳,没有返回0
     * @return int
     */
    public static function fetchUserFirstDownloadTime($userId)
    {
        return UserFactory::fetchUserFirstDownloadProductInfo($userId);
    }

    /**
     * 根据设备ID获取用户地域
     *
     * @param string $deviceId
     * @return int
     */
    public static function getUserLocationIdByDeviceId($deviceId)
    {
        $devices = (int) DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);

        return $devices;
    }

}