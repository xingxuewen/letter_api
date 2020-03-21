<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataBairongLog;
use App\Models\Orm\DataBannerCreditCardLog;
use App\Models\Orm\DataCreditcardConfigLog;
use App\Models\Orm\DataPosLog;
use App\Models\Orm\DataProductDownloadLog;
use App\Models\Orm\DataShadowCreditcardConfigLog;
use App\Models\Orm\DataSpreadConfigLog;
use App\Models\Orm\PlatformProductArea;
use App\Models\Orm\SpreadConfig;
use App\Models\Orm\UserAreas;
use App\Models\Orm\UserDeviceLocation;
use App\Models\Orm\UserDeviceLocationLog;
use App\Models\Orm\UserIdfa;

/**
 * Data工厂
 * Class DataFactory
 * @package App\Models\Factory
 */
class DataFactory extends AbsModelFactory
{
    /**
     * post机申请流水
     *
     * @param array $data
     * @return bool
     */
    public static function insertPostLog($data = [])
    {
        $messages = new DataPosLog();
        $messages->name = $data['name'];
        $messages->mobile = $data['mobile'];
        $messages->address = $data['address'];
        $messages->content = $data['content'];
        $messages->created_at = date('Y-m-d H:i:s', time());
        $messages->created_ip = Utils::ipAddress();

        return $messages->save();
    }

    /**
     * 记录百融信息流水
     *
     * @param array $data
     * @return bool
     */
    public static function insertBairongLog($data = [])
    {
        $messages = new DataBairongLog();
        $messages->user_type = $data['user_type'];
        $messages->error_code = isset($data['code']) ? $data['code'] : 0;
        $messages->swift_number = isset($data['swift_number']) ? $data['swift_number'] : 0;
        $messages->mobile = $data['mobile'];
        $messages->content = $data['content'];
        $messages->created_at = date('Y-m-d H:i:s', time());
        $messages->created_ip = Utils::ipAddress();

        return $messages->save();
    }

    /**
     * 根据投放标识、用户id查数据
     * @param array $params
     * @return array
     */
    public static function fetchUserIdfaByUserIdEmpty($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'app_name' => $params['appName'], 'user_id' => 0])
            ->limit(1)
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 根据投放标识、用户id存在时查数据
     *
     * @param array $params
     * @return array
     */
    public static function fetchUserIdfaByUserId($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'app_name' => $params['appName'], 'user_id' => $params['userId']])
            ->limit(1)
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 根据投放标识查数据
     * @param array $params
     * @return array
     */
    public static function fetchUserIdfaByIdfaid($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId']])
            ->limit(1)
            ->first();

        return $model ? $model->toArray() : [];
    }

    /**
     * 创建投放数据
     * @param array $params
     * @return bool
     */
    public static function createUserIdfa($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'app_name' => $params['appName'],])
            ->limit(1)
            ->first();
        if (empty($model)) {
            $model = new UserIdfa();
            $model->status = 1;         //0推广idfa，1自然量
            $model->app_name = $params['appName'];
            $model->source = $params['source'];
        }

        $model->idfa_id = $params['idfaId'];
        $model->app_name = isset($params['appName']) ? $params['appName'] : '';
        $model->source = isset($params['source']) ? $params['source'] : '';
        $model->user_id = $params['userId'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }

    /**
     * 标识、用户信息
     * @param array $params
     * @return bool
     */
    public static function updateUserIdfaByIds($params = [])
    {
        $model = UserIdfa::where(['idfa_id' => $params['idfaId'], 'app_name' => $params['appName'], 'user_id' => $params['userId']])
            ->limit(1)
            ->first();

        if (empty($model)) {
            $model = new UserIdfa();
            $model->status = 1;         //0推广idfa，1自然量
            $model->app_name = $params['appName'];
            $model->source = $params['source'];
        }

        $model->idfa_id = $params['idfaId'];
        $model->user_id = $params['userId'];
        $model->updated_at = date('Y-m-d H:i:s', time());
        $model->updated_ip = Utils::ipAddress();

        return $model->save();
    }

    /**
     * 一键选贷款点击流水
     *
     * @param array $params
     * @return bool
     */
    public static function createDataSpreadConfigLog($params = [])
    {
        //一键选贷款信息
        $config = $params['config'];
        //渠道信息
        $delivery = $params['delivery'];

        $log = new DataSpreadConfigLog();
        $log->user_id = $params['userId'];
        $log->config_id = $config['id'];
        $log->type_id = $config['type_id'];
        $log->type_nid = $config['type_nid'];
        $log->button_title = isset($config['button_title']) ? $config['button_title'] : '';
        $log->button_subtitle = isset($config['button_subtitle']) ? $config['button_subtitle'] : '';
        $log->title = isset($config['title']) ? $config['title'] : '';
        $log->url = isset($params['url']) ? $params['url'] : $config['url'];
        $log->channel_id = isset($delivery['id']) ? $delivery['id'] : '';
        $log->channel_title = isset($delivery['title']) ? $delivery['title'] : '';
        $log->channel_nid = isset($delivery['nid']) ? $delivery['nid'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 一键选贷款配置总计数
     *
     * @param string $id
     * @return mixed
     */
    public static function updateSpreadConfigClickCount($id = '')
    {
        $res = SpreadConfig::where(['id' => $id])->orderBy('updated_at', 'desc')->first();
        $res->increment('click_count', 1);

        return $res->save();
    }

    /**
     * 广告点击流水统计
     *
     * @param array $params
     * @return bool
     */
    public static function createDataBannerCreditCardLog($params = [])
    {
        //广告信息
        $card = $params['card'];
        //渠道信息
        $delivery = $params['delivery'];

        $log = new DataBannerCreditCardLog();
        $log->user_id = $params['userId'];
        $log->card_id = $card['id'];
        $log->card_type_id = $card['type_id'];
        $log->type_nid = isset($card['type_nid']) ? $card['type_nid'] : '';
        $log->name = isset($card['name']) ? $card['name'] : '';
        $log->title = isset($card['title']) ? $card['title'] : '';
        $log->subtitle = isset($card['subtitle']) ? $card['subtitle'] : '';
        $log->url = isset($card['url']) ? $card['url'] : '';
        $log->app_link = isset($card['app_link']) ? $card['app_link'] : '';
        $log->h5_link = isset($card['h5_link']) ? $card['h5_link'] : '';
        $log->product_list = isset($card['product_list']) ? $card['product_list'] : '';
        $log->position = isset($card['position']) ? $card['position'] : '';
        $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
        $log->channel_id = isset($delivery['id']) ? $delivery['id'] : '';
        $log->channel_title = isset($delivery['title']) ? $delivery['title'] : '';
        $log->channel_nid = isset($delivery['nid']) ? $delivery['nid'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 信用卡点击流水统计
     *
     * @param array $params
     * @return bool
     */
    public static function createDataCreditcardConfigLog($params = [])
    {
        //一键选贷款信息
        $config = $params['config'];
        //渠道信息
        $delivery = $params['delivery'];

        $log = new DataCreditcardConfigLog();
        $log->user_id = $params['userId'];
        $log->config_id = $config['id'];
        $log->type_id = $config['type_id'];
        $log->type_nid = $config['type_nid'];
        $log->button_title = isset($config['button_title']) ? $config['button_title'] : '';
        $log->button_subtitle = isset($config['button_subtitle']) ? $config['button_subtitle'] : '';
        $log->title = isset($config['title']) ? $config['title'] : '';
        $log->url = isset($params['url']) ? $params['url'] : $config['url'];
        $log->channel_id = isset($delivery['id']) ? $delivery['id'] : '';
        $log->channel_title = isset($delivery['title']) ? $delivery['title'] : '';
        $log->channel_nid = isset($delivery['nid']) ? $delivery['nid'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 马甲信用卡点击流水统计
     *
     * @param array $params
     * @return bool
     */
    public static function createShadowDataCreditcardConfigLog($params = [])
    {
        //一键选贷款信息
        $config = $params['config'];
        //渠道信息
        $delivery = $params['delivery'];
        //用户信息
        $user = $params['user'];

        $log = new DataShadowCreditcardConfigLog();
        $log->user_id = $params['userId'];
        $log->username = $user['username'];
        $log->mobile = $user['mobile'];
        $log->config_id = $config['id'];
        $log->shadow_id = $params['shadowId'];
        $log->type_nid = $config['type_nid'];
        $log->button_title = isset($config['button_title']) ? $config['button_title'] : '';
        $log->button_subtitle = isset($config['button_subtitle']) ? $config['button_subtitle'] : '';
        $log->title = isset($config['title']) ? $config['title'] : '';
        $log->url = isset($params['url']) ? $params['url'] : $config['url'];
        $log->channel_id = isset($delivery['id']) ? $delivery['id'] : '';
        $log->channel_title = isset($delivery['title']) ? $delivery['title'] : '';
        $log->channel_nid = isset($delivery['nid']) ? $delivery['nid'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * 产品下载监测流水统计
     *
     * @param array $params
     * @return bool
     */
    public static function createDataProductDownloadLog($params = [])
    {
        //用户
        $users = $params['users'];
        //产品
        $products = $params['products'];
        //渠道信息
        $deliverys = $params['deliverys'];

        $log = new DataProductDownloadLog();
        $log->user_id = isset($params['userId']) ? $params['userId'] : '';
        $log->username = isset($users['username']) ? $users['username'] : '';
        $log->mobile = isset($users['mobile']) ? $users['mobile'] : '';
        $log->platform_id = isset($products['platform_id']) ? $products['platform_id'] : '';
        $log->platform_product_id = isset($products['platform_product_id']) ? $products['platform_product_id'] : '';
        $log->platform_product_name = isset($products['platform_product_name']) ? $products['platform_product_name'] : '';
        $log->product_is_vip = isset($params['product_is_vip']) ? $params['product_is_vip'] : '99';
        $log->product_is_absorb_num = isset($products['product_is_absorb_num']) ? $products['product_is_absorb_num'] : '99';
        $log->click_source = isset($params['clickSource']) ? $params['clickSource'] : '';
        $log->position = isset($products['position_sort']) ? $products['position_sort'] : 0;
        $log->channel_id = isset($deliverys['id']) ? $deliverys['id'] : '';
        $log->channel_title = isset($deliverys['title']) ? $deliverys['title'] : '';
        $log->channel_nid = isset($deliverys['nid']) ? $deliverys['nid'] : '';
        $log->shadow_nid = isset($params['shadowNid']) ? $params['shadowNid'] : '';
        $log->app_name = isset($params['appName']) ? $params['appName'] : '';
        $log->status = isset($params['status']) ? $params['status'] : 0;
        $log->device_id = isset($params['deviceId']) ? $params['deviceId'] : '';
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->create_at = date('Y-m-d H:i:s', time());
        $log->create_ip = Utils::ipAddress();
        return $log->save();
    }

}