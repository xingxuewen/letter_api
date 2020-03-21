<?php

namespace App\Services\Core\Shadow\Bank;

use App\Constants\OauthConstant;
use App\Services\AppService;
use App\Services\Core\Platform\Kami\Kami\KamiService;

/**
 * 马甲银行、信用卡配置对接
 *
 * Class SpreadService
 * @package App\Services\Core\Spreadconfig
 */
class ShadowBankService extends AppService
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
     * @param $datas
     * @return mixed
     * 获取对接url
     */
    public function toSpreadService($datas)
    {
        $typeNid = isset($datas['config']['type_nid']) ? $datas['config']['type_nid'] : '';
        $urls['url'] = isset($datas['config']['url']) ? $datas['config']['url'] : '';
        //默认地址
        $datas['page'] = isset($datas['config']['url']) ? $datas['config']['url'] : '';

        //唯一标识处理
        $typeNids = explode('_', $typeNid);
        $typeNid = array_slice($typeNids, -1, 1);
        $typeNid = $typeNid[0];

        // 对接
        switch ($typeNid) {
            case 'kami':
                $datas['type'] = OauthConstant::KAMI_LOGIN_TYPE;
                $pageData = KamiService::fetchKamiUrl($datas);
                break;
            default:
                $pageData = [];
        }

        $urls['url'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];

        return $urls;
    }

}