<?php

namespace App\Services\Core\User\Tools;

use App\Constants\OauthConstant;
use App\Constants\ToolsConstant;
use App\Models\Factory\ToolsFactory;
use App\Services\AppService;
use App\Services\Core\Platform\Kami\Kami\KamiService;

class ToolsService extends AppService
{
    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }


    /**
     * 对接
     *
     * @param array $datas
     * @return mixed
     */
    public function toToolsService($datas = [])
    {
        $typeNid = isset($datas['tools']['type_nid']) ? $datas['tools']['type_nid'] : '';
        $urls['app_link'] = isset($datas['tools']['app_link']) ? $datas['tools']['app_link'] : '';
        $urls['h5_link'] = isset($datas['tools']['h5_link']) ? $datas['tools']['h5_link'] : '';
        //默认地址
        $datas['page'] = isset($datas['tools']['app_link']) ? $datas['tools']['app_link'] : '';

        //判断是否需要对接
        $tools = ToolsFactory::fetchToolsById($datas);
        if (!$tools || $tools['is_login'] == 0) {
            return $urls;
        }

        // 对接 - 唯一标识对应
        switch ($typeNid) {
            case 'kami': // 卡秘 - 办卡
                $datas['type'] = OauthConstant::KAMI_LOGIN_TYPE;
                $pageData = KamiService::fetchKamiUrl($datas);
                break;
            case 'quxian': // 卡秘 - 取现
                $datas['type'] = OauthConstant::KAMI_CASH_LOGIN_TYPE;
                $pageData = KamiService::fetchKamiUrl($datas);
                break;
            default:
                $pageData = [];

        }

        $urls['app_link'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];
        $urls['h5_link'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];

        return $urls;

    }

}


