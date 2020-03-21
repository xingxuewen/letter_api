<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataQuickloanConfigLog;
use App\Models\Orm\QuickloanConfig;
use App\Models\Orm\QuickloanConfigType;

/**
 * 极速贷
 *
 * Class QuickloanFactory
 * @package App\Models\Factory
 */
class QuickloanFactory extends AbsModelFactory
{

    /**
     * 极速贷
     * 根据唯一标识获取极速贷类型表中的主id
     *
     * @param array $params
     * @return string
     */
    public static function fetchQuickloanConfigTypeIdByNid($params = [])
    {
        $id = QuickloanConfigType::select(['id'])
            ->where(['type_nid' => $params['quickloanNid'], 'status' => 1])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * 极速贷配置
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchQuickloanConfigById($typeId = '')
    {
        $config = QuickloanConfig::select(['id', 'button_title', 'button_subtitle', 'title', 'url', 'is_login'])
            ->where(['is_show' => 1])
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        return $config ? $config->toArray() : [];
    }

    /**
     * 极速贷配置
     *
     * @param string $id
     * @return array
     */
    public static function fetchQuickloanConfigInfoById($id = '')
    {
        $config = QuickloanConfig::select(['id', 'type_id', 'type_nid', 'button_title', 'button_subtitle', 'title', 'url', 'is_login'])
            ->where(['is_show' => 1, 'id' => $id])
            ->first();

        return $config ? $config->toArray() : [];
    }

    /**
     * 极速贷点击流水
     *
     * @param array $params
     * @param array $deliveryArr
     * @return bool
     */
    public static function createDataQuickloanConfigLog($params = [], $deliveryArr = [])
    {
        $config = isset($params['config']) ? $params['config'] : [];
        $log = new DataQuickloanConfigLog();
        $log->user_id = isset($params['userId']) ? $params['userId'] : '';
        $log->config_id = isset($params['configId']) ? $params['configId'] : '';
        $log->type_id = isset($config['type_id']) ? $config['type_id'] : '';
        $log->type_nid = isset($config['type_nid']) ? $config['type_nid'] : '';
        $log->button_title = isset($config['button_title']) ? $config['button_title'] : '';
        $log->button_subtitle = isset($config['button_subtitle']) ? $config['button_subtitle'] : '';
        $log->title = isset($config['title']) ? $config['title'] : '';
        $log->url = isset($config['url']) ? $config['url'] : '';
        $log->channel_id = isset($deliveryArr['id']) ? $deliveryArr['id'] : '';
        $log->channel_title = isset($deliveryArr['title']) ? $deliveryArr['title'] : '';
        $log->channel_nid = isset($deliveryArr['nid']) ? $deliveryArr['nid'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 极速贷配置点击统计
     *
     * @param array $params
     * @return mixed
     */
    public static function updateConfigClickCountById($params = [])
    {
        $query = QuickloanConfig::select(['id'])
            ->where(['id' => $params['configId'], 'is_show' => 1])
            ->first();

        $query->increment('click_count', 1);

        return $query->save();
    }
}