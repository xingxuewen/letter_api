<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataCooperateProductApplyLog;
use App\Models\Orm\DataProductApplyGonggeLog;
use App\Models\Orm\DataProductApplyLog;
use App\Models\Orm\DeliveryCount;
use App\Models\Orm\DeliveryLog;
use App\Helpers\Logger\SLogger;
use App\Models\Orm\UserDelivery;
use App\Models\Orm\UserShadow;

/**
 * 渠道数据统计
 */
class DeliveryFactory extends AbsModelFactory
{

    /**
     * 渠道注册统计
     * 1 点击 2 注册
     *
     * @param $register
     * @param int $userId
     */
    public static function fetchDeliveryRegister($register, $userId = 0)
    {
        $channel_fr = isset($register['channel_fr']) ? $register['channel_fr'] : 'channel_2';
        if (isset($channel_fr)) {
            $re = DeliveryFactory::getDeliverByNid($channel_fr);
            if (!$re) {
                $re = DeliveryFactory::getDeliverByNid('chanel_2');
                $channel_fr = 'channel_2';
            }
            $re->register += 1;
            $re->data = date('Y-m-d H:i:s', time());
            $re->save();
        }
    }

    /**
     * 获取渠道Nid
     * @param $userId
     * @return string
     */
    public static function fetchDeliveryNId($userId)
    {
        $deliveryArr = UserDelivery::select(['delivery_id'])
            ->where(['user_id' => $userId])
            ->first();
        if ($deliveryArr) {
            $deliveryCount = DeliveryCount::select(['id'])
                ->where(['id' => $deliveryArr->delivery_id])
                ->first();
            return $deliveryCount ? $deliveryCount->id : 0;
        }
        return 0;
    }

    /**
     * @param $count
     * @return array
     * 渠道注册统计日志是否重复
     * type:2  注册
     */
    public static function fetchRegisterDeliveryLog($data)
    {
        $registerDeliveryLog = DeliveryLog::select()
            ->where(['type' => 2, 'user_id' => $data['user_id']])
            ->first();

        return $registerDeliveryLog ? $registerDeliveryLog->toArray() : [];
    }

    /**
     * 添加渠道统计流水数据
     * @param $channerl_fr
     * @param $userId ,
     */
    public static function insertDeliverylLog($count)
    {
        $re = DeliveryCount::where('nid', '=', $count['channel_fr'])->first();
        if ($re) {
            // 历史原因 channel_nid 和 channel_id值记录反了
            $delivery = new DeliveryLog();
            $delivery->channel_id = $count['channel_fr'];
            $delivery->channel_nid = $re->id;
            $delivery->create_time = time();
            $delivery->shadow_nid = isset($count['shadow_nid']) ? $count['shadow_nid'] : 'sudaizhijia';
            $delivery->type = 2;
            $delivery->user_id = $count['userId'];
            $delivery->client_type = $count['version'];
            $delivery->user_agent = UserAgent::i()->getUserAgent();
            $delivery->created_ip = Utils::ipAddress();
            $delivery->create_date = date('Y-m-d H:i:s', time());
            return $delivery->save();
        }
        return false;
    }

    /**
     * 通过渠道标识nid获取渠道数据
     *
     * @param $nid
     * @return array
     */
    public static function getDeliveryByNid($nid)
    {
        $deliverys = DeliveryCount::where('nid', '=', $nid)->first();

        return $deliverys ? $deliverys->toArray() : [];
    }

    /**
     * 注册时渠道统计---更新统计主表中总数据
     * @param $register
     * @param int $userId
     */
    public static function updateDeliveryRegisterCount($channel_fr)
    {
        $re = DeliveryCount::where('nid', '=', $channel_fr)->first();
        $re->register += 1;
        $re->date = date('Y-m-d H:i:s', time());
        if ($re->save()) {
            logInfo('注册渠道统计成功！');
            return true;
        } else {
            logError('注册渠道统计失败！');
            return false;
        }
        return false;
    }

    /**
     * @param $nid
     * @return int
     * 获取渠道号对应渠道id
     */
    public static function fetchChannelId($nid)
    {
        $channelObj = DeliveryCount::select(['id'])
            ->where(['nid' => $nid])
            ->first();

        return $channelObj ? $channelObj->id : '';
    }

    /**
     * @param $count
     * 注册时添加用户渠道关系
     */
    public static function createUserDelivery($userId, $channelId)
    {
        $userDelivery = UserDelivery::where(['user_id' => $userId])->first();
        if (empty($userDelivery)) {
            $userDelivery = new UserDelivery();
            $userDelivery->create_at = date('Y-m-d H:i:s', time());
            $userDelivery->create_ip = Utils::ipAddress();
            $userDelivery->user_id = $userId;
            $userDelivery->delivery_id = $channelId;

            return $userDelivery->save();
        }
        return $userDelivery;
    }

    /**
     * @param $userId
     * 获取渠道Id
     */
    public static function fetchDeliveryId($userId)
    {
        $deliveryArr = UserDelivery::select(['delivery_id'])
            ->where(['user_id' => $userId])
            ->first();
        return $deliveryArr ? $deliveryArr->delivery_id : 80;
    }


    /** 通过用户id获取用户渠道
     * @param $userId
     * @return int|mixed
     */
    public static function fetchShadowDeliveryId($userId, $shadowId)
    {
        $deliveryArr = UserShadow::select(['delivery_id'])
            ->where(['user_id' => $userId, 'shadow_id' => $shadowId])
            ->first();
        return $deliveryArr ? $deliveryArr->delivery_id : 80;
    }

    /**
     * @param $userId
     * 获取渠道Id
     */
    public static function fetchDeliveryIdToNull($userId)
    {
        $deliveryArr = UserDelivery::select(['delivery_id'])
            ->where(['user_id' => $userId])
            ->first();
        return $deliveryArr ? $deliveryArr->delivery_id : '';
    }

    /**
     * @param $deliveryId
     * 获取渠道信息
     */
    public static function fetchDeliveryArray($deliveryId)
    {
        $deliveryArr = DeliveryCount::select(['id', 'title', 'nid'])
            ->where(['id' => $deliveryId])
            ->first();
        return $deliveryArr ? $deliveryArr->toArray() : [];
    }

    /**
     * @param $userId
     * @param $productId
     * @return bool
     * 查询该用户在当前产品今天是否有过申请记录
     */
    public static function fetchProductApplyLog($userId,$productId)
    {

        $data = DataProductApplyLog::select(['id'])
                                   ->where(['user_id' => $userId,'platform_product_id' => $productId])
                                   ->where('create_at', '>=', date('Y-m-d 00:00:00'))
                                   ->first();
        return $data ? true : false;
    }

    /**
     * 产品申请点击流水统计
     *
     * @param $userId
     * @param $userArr
     * @param $productArr
     * @param $deliveryArr
     * @param array $data
     * @return bool
     */
    public static function createProductApplyLog($userId, $userArr, $productArr, $deliveryArr, $data = [])
    {
        $productApplyLogObj = new DataProductApplyLog();
        $productApplyLogObj->user_id = $userId;
        $productApplyLogObj->username = $userArr['username'];
        $productApplyLogObj->mobile = $userArr['mobile'];
        $productApplyLogObj->click_source = isset($data['clickSource']) ? $data['clickSource'] : '';
        $productApplyLogObj->platform_id = $productArr['platform_id'];
        $productApplyLogObj->platform_product_id = $productArr['platform_product_id'];
        $productApplyLogObj->platform_product_name = $productArr['platform_product_name'];
        $productApplyLogObj->product_is_vip = isset($data['is_vip_product']) ? $data['is_vip_product'] : 99;
        $productApplyLogObj->position = $productArr['position_sort'];
        $productApplyLogObj->channel_id = $deliveryArr['id'];
        $productApplyLogObj->channel_title = $deliveryArr['title'];
        $productApplyLogObj->channel_nid = $deliveryArr['nid'];
        $productApplyLogObj->create_at = date('Y-m-d H:i:s', time());
        $productApplyLogObj->create_ip = Utils::ipAddress();
        $productApplyLogObj->user_agent = UserAgent::i()->getUserAgent();
        $productApplyLogObj->terminal_type = getOsType();

        if(!self::fetchProductApplyLog($userId,$productArr['platform_product_id'])) {
            $productApplyLogObj->first_count = 1;
        } else {
            $productApplyLogObj->first_count = 0;
        }

        return $productApplyLogObj->save();
    }

    /**
     * 合作贷立即申请点击流水
     *
     * @param $userId
     * @param $userArr
     * @param $productArr
     * @param $deliveryArr
     * @return bool
     */
    public static function createCoopeProductApplyLog($userId, $userArr, $productArr, $deliveryArr)
    {
        $productApplyLogObj = new DataCooperateProductApplyLog();
        $productApplyLogObj->user_id = $userId;
        $productApplyLogObj->username = $userArr['username'];
        $productApplyLogObj->mobile = $userArr['mobile'];
        $productApplyLogObj->platform_id = $productArr['platform_id'];
        $productApplyLogObj->platform_product_id = $productArr['platform_product_id'];
        $productApplyLogObj->platform_product_name = $productArr['platform_product_name'];
        $productApplyLogObj->channel_id = $deliveryArr['id'];
        $productApplyLogObj->channel_title = $deliveryArr['title'];
        $productApplyLogObj->channel_nid = $deliveryArr['nid'];
        $productApplyLogObj->create_at = date('Y-m-d H:i:s', time());
        $productApplyLogObj->create_ip = Utils::ipAddress();
        $productApplyLogObj->user_agent = UserAgent::i()->getUserAgent();
        return $productApplyLogObj->save();
    }

    /**
     * @param $productArr
     * 宫格产品申请点击流水统计
     */
    public static function createProductApplyGonggeLog($productArr)
    {
        $gonggeLog = new DataProductApplyGonggeLog();
        $gonggeLog->platform_id = $productArr['platform_id'];
        $gonggeLog->platform_product_id = $productArr['platform_product_id'];
        $gonggeLog->platform_product_name = $productArr['platform_product_name'];
        $gonggeLog->platform_url = $productArr['platform_url'];
        $gonggeLog->user_agent = $productArr['user_agent'];
        $gonggeLog->create_at = date('Y-m-d H:i:s', time());
        $gonggeLog->create_ip = Utils::ipAddress();
        return $gonggeLog->save();
    }

    /**
     * @param $userId
     * @return int|mixed
     * 根据用户id获取渠道表主id
     */
    public static function fetchDeliveryIdByUserId($userId)
    {
        $id = UserDelivery::select(['id'])
            ->where(['user_id' => $userId])
            ->first();

        return $id ? $id->id : 0;
    }

    /** 马甲产品申请点击流水统计
     * @param array $data
     * @return bool
     */
    public static function createShadowProductApplyLog($data = [])
    {
        if (!empty($data)) {
            $productApplyLogObj = new DataProductApplyLog();
            $productApplyLogObj->user_id = $data['user_id'];
            $productApplyLogObj->username = $data['username'];
            $productApplyLogObj->mobile = $data['mobile'];
            $productApplyLogObj->platform_id = $data['platform_id'];
            $productApplyLogObj->platform_product_id = $data['platform_product_id'];
            $productApplyLogObj->platform_product_name = $data['platform_product_name'];
            $productApplyLogObj->product_is_vip = $data['product_is_vip'];
            $productApplyLogObj->click_source = isset($data['click_source']) ? $data['click_source'] : '';
            $productApplyLogObj->position = isset($data['position']) ? $data['position'] : '99';
            $productApplyLogObj->channel_id = $data['channel_id'];
            $productApplyLogObj->channel_title = $data['channel_title'];
            $productApplyLogObj->channel_nid = $data['channel_nid'];
            $productApplyLogObj->shadow_nid = $data['shadow_nid'];
            $productApplyLogObj->create_at = $data['create_at'];
            $productApplyLogObj->create_ip = $data['create_ip'];
            $productApplyLogObj->user_agent = UserAgent::i()->getUserAgent();
            return $productApplyLogObj->save();
        }

        return false;
    }

}
