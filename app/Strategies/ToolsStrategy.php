<?php

namespace App\Strategies;

use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 工具集策略
 *
 * Class ToolsStrategy
 * @package App\Strategies
 */
class ToolsStrategy extends AppStrategy
{
    /**
     * 工具集
     *
     * @param array $datas
     * @return array
     */
    public static function getTools($datas = [])
    {
        foreach ($datas as $key => $val) {
            $datas[$key]['img'] = QiniuService::getImgs($val['img']);
        }

        return $datas ? $datas : [];
    }

    /**
     * 对接数据整理
     *
     * @param $data
     * @param $user
     * @param $toolsInfo
     * @return array
     */
    public static function getOauthToolsDatas($data, $user, $toolsInfo)
    {
        $data['user']['username'] = isset($user['user']['username']) ? $user['user']['username'] : '';
        $data['user']['mobile'] = isset($user['user']['mobile']) ? $user['user']['mobile'] : '';
        $data['user']['sex'] = isset($user['profile']['sex']) ? $user['profile']['sex'] : '';
        $data['user']['real_name'] = isset($user['profile']['real_name']) ? $user['profile']['real_name'] : '';
        $data['user']['idcard'] = isset($user['profile']['identity_card']) ? $user['profile']['identity_card'] : '';

        //工具数据
        $data['tools']['id'] = isset($toolsInfo['id']) ? $toolsInfo['id'] : '';
        $data['tools']['title'] = isset($toolsInfo['title']) ? $toolsInfo['title'] : '';
        $data['tools']['subtitle'] = isset($toolsInfo['subtitle']) ? $toolsInfo['subtitle'] : '';
        $data['tools']['type_nid'] = isset($toolsInfo['type_nid']) ? $toolsInfo['type_nid'] : '';
        $data['tools']['img'] = isset($toolsInfo['img']) ? $toolsInfo['img'] : '';
        $data['tools']['is_login'] = isset($toolsInfo['is_login']) ? $toolsInfo['is_login'] : '';
        $data['tools']['is_abut'] = isset($toolsInfo['is_abut']) ? $toolsInfo['is_abut'] : '';
        $data['tools']['web_switch'] = isset($toolsInfo['web_switch']) ? $toolsInfo['web_switch'] : '';
        $data['tools']['app_link'] = isset($toolsInfo['app_link']) ? $toolsInfo['app_link'] : '';
        $data['tools']['h5_link'] = isset($toolsInfo['h5_link']) ? $toolsInfo['h5_link'] : '';

        return $data ? $data : [];
    }
}