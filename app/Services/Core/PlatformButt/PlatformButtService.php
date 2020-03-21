<?php

namespace App\Services\Core\PlatformButt;

use App\Models\Factory\OauthFactory;
use App\Services\AppService;
use App\Services\Core\PlatformButt\Doubei\Doubei\DoubeiService;
use App\Services\Core\PlatformButt\Qianzhouzhou\QianzhouzhouService;
use App\Services\Core\PlatformButt\Danhuahua\Danhuahua\DanhuahuaService;
use App\Services\Core\PlatformButt\Fangsiling\Fangsiling\FangsilingService;

class PlatformButtService extends AppService
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
     * 判断产品是否配置了需要撞库，根据结果进行如下操作：
     * 如果没配置，或者没有类型id，返回：不需要撞库 + 产品地址
     * 如果配置了,并且有类型id,根据类型id进行撞库，返回：需要撞库 + 产品地址
     * @param $datas
     * @return mixed
     */
    public function toPlatformButtService($datas)
    {
        //默认url
        $pageData['url'] = $datas['page'];

        //类型id
        $typeNid = $datas['product']['type_nid'];

        //产品id
        $productId = $datas['productId'];

        //如果不要求产品是上线状态
        if (isset($datas['is_nothing']) && $datas['is_nothing'] == 1) {
            //判断产品是否配置了需要撞库
            $isButt = 1; //是否验证撞库开关 1开
            $isButt = OauthFactory::checkProductIsButtNothing($productId, $isButt);

        } else {
            //判断产品是否配置了需要撞库
            $isButt = 1; //是否验证撞库开关 1开
            $isButt = OauthFactory::checkProductIsButt($productId, $isButt);
        }

        //产品是否配置了需要撞库
        $pageData['is_butt'] = isset($isButt['is_butt']) ? $isButt['is_butt'] : 0;

        //如果没配置需要撞库或者没有类型id，返回不需要撞库+产品地址
        if (!$isButt || empty($typeNid)) {
            return $pageData;
        }

        //执行撞库，返回需要撞库+产品地址
        switch ($typeNid) {
            case 'DB': //抖贝
                $pageData = DoubeiService::fetchDoubeiButt($datas);
                break;
            case 'QZZ': //钱周周
                $pageData = QianzhouzhouService::fetchQianzhouzhouUrl($datas);
                break;
            case 'DHHH': //蛋花花
                $pageData = DanhuahuaService::fetchDanhuahuaUrl($datas);
                break;
            case 'FSL': //房司令
                $pageData = FangsilingService::fetchFangsilingUrl($datas);
                break;
            default:
                $pageData['apply_url'] = $datas['page'];
                break;
        }

        $pageData['url'] = isset($pageData['apply_url']) ? $pageData['apply_url'] : $datas['page'];
        $pageData['is_butt'] = $isButt['is_butt'];

        return $pageData;
    }
}