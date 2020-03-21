<?php

namespace App\Services\Core\User\Privilege;

use App\Constants\OauthConstant;
use App\Constants\PrivilegeConstant;
use App\Models\Factory\OauthFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Platform\Kami\Kami\KamiService;
use DB;

/**
 * 合作贷对接产品
 *
 * Class OneloanApplyService
 * @package App\Services\Core\SdPlatform
 */
class PrivilegeService extends AppService
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
     * 获取url
     *
     * @param $datas
     * @return mixed
     */
    public function toPrivilegeService($datas)
    {
        $typeNid = isset($datas['privilege']['type_nid']) ? $datas['privilege']['type_nid'] : '';
        $urls['url'] = isset($datas['privilege']['url']) ? $datas['privilege']['url'] : '';
        $datas['page'] = isset($datas['privilege']['url']) ? $datas['privilege']['url'] : '';

        //判断是否需要对接
        $privilege = UserVipFactory::getPrivilege($datas['privilegeId']);
        if (!$privilege || $privilege['is_abut'] == 0) {
            return $urls;
        }

        // 对接 - 唯一标识对应
        switch ($typeNid) {
            case 'vip_kami': //卡秘
                $datas['type'] = OauthConstant::KAMI_CASH_LOGIN_TYPE;
                $pageData = KamiService::fetchKamiUrl($datas);
                break;
            default:
                $pageData = [];
        }

        $urls['url'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];

        return $urls;
    }

}
