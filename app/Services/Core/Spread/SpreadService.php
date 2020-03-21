<?php

namespace App\Services\Core\Spread;

use App\Services\AppService;
use App\Services\Core\Spread\Rong360\Rong360Service;
use App\Services\Core\Spread\Jiufuwanka\Xianjin\JiufuwankaxianjinService;

/**
 * 一键配置对接
 *
 * Class SpreadService
 * @package App\Services\Core\Spreadconfig
 */
class SpreadService extends AppService
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
        $page = isset($datas['config']['url']) ? $datas['config']['url'] : '';

        // 对接
        switch ($typeNid) {
            case 'rong360': //融360
                $urls = Rong360Service::fetchRong360Url($datas);
                break;
            case 'jiufuwanka': //玖富万卡现金
                $urls = JiufuwankaxianjinService::fetchJiufuwankaUrl($datas);
                break;
            default:
                $urls['url'] = $page;
                break;
        }

        return $urls;
    }

}