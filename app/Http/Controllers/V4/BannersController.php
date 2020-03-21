<?php

namespace App\Http\Controllers\V4;

use App\Constants\BannersConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\ComModelFactory;
use App\Models\Factory\BannersFactory;
use App\Strategies\BannerStrategy;
use Illuminate\Http\Request;

/**
 * Banners
 */
class BannersController extends Controller
{
    /**
     * 首页分类专题
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecials()
    {
        //分类专题
        $typeNid = BannersConstant::BANNER_CAROUSEL_SPECIAL;
        //根据唯一标识typeNid 查询类型id
        $status = 1; //显示
        $typeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $specials = BannersFactory::fetchCashBanners($typeId);

        //暂无数据
        if (empty($specials) || empty($typeId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $cashData = BannerStrategy::getSpecialsAndRecommends($specials);

        return RestResponseFactory::ok($cashData);
    }


}