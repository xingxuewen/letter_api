<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataToolsApplyLog;
use App\Models\Orm\Tools;
use App\Models\Orm\ToolsType;

/**
 * 工具集工厂
 *
 * Class ToolsFactory
 * @package App\Models\Factory
 */
class ToolsFactory extends AbsModelFactory
{
    /**
     * 工具集类型id
     * 查询条件：nid、状态
     *
     * @param string $nid
     * @return string
     */
    public static function fetchToolsTypeIdByNid($nid = '')
    {
        $id = ToolsType::select(['id'])
            ->where(['type_nid' => $nid, 'status' => 1])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * 工具集数据
     * 筛选条件：类型id、状态
     * 排序条件：位置正序、id倒序
     *
     * @param string $typeId
     * @return array
     */
    public static function fetchToolsByTypeId($typeId = '')
    {
        $tools = Tools::select(['id', 'title', 'subtitle', 'img', 'is_login', 'is_abut', 'web_switch'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->orderBy('position', 'asc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return $tools ? $tools : [];
    }

    /**
     * 工具详情数据
     * 筛选条件：主键id、状态
     *
     * @param array $datas
     * @return array
     */
    public static function fetchToolsById($datas = [])
    {
        $tools = Tools::select(['id', 'title', 'subtitle', 'type_nid', 'img', 'is_login', 'is_abut', 'web_switch', 'app_link', 'h5_link'])
            ->where(['id' => $datas['toolsId'], 'status' => 1])
            ->first();

        return $tools ? $tools->toArray() : [];
    }

    /**
     * 工具点击流水统计
     *
     * @param array $datas
     * @return bool
     */
    public static function createDataToolsApplyLog($datas = [])
    {
        $log = new DataToolsApplyLog();
        $log->user_id = $datas['userId'];
        $log->username = $datas['user']['username'];
        $log->mobile = $datas['user']['mobile'];
        $log->tool_id = $datas['tools']['id'];
        $log->tool_type_nid = $datas['tools']['type_nid'];
        $log->title = $datas['tools']['title'];
        $log->subtitle = $datas['tools']['subtitle'];
        $log->app_link = $datas['app_link'];
        $log->h5_link = $datas['h5_link'];
        $log->channel_id = $datas['delivery']['id'];
        $log->channel_title = $datas['delivery']['title'];
        $log->channel_nid = $datas['delivery']['nid'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }
}