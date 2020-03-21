<?php

namespace App\Http\Controllers\V2;

use App\Constants\NoticeConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\NoticeFactory;
use App\Strategies\NoticeStrategy;
use Illuminate\Http\Request;

/**
 * 通知
 *
 * Class NoticeController
 * @package App\Http\Controllers\V2
 */
class NoticeController extends Controller
{
    /**
     * 通知
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchNoticeLists(Request $request)
    {
        $indent = $request->user()->indent;

        //筛选条件
        $versionCode = NoticeConstant::NOTICE_VERSION_CODE;
        //查询广告通知
        $noticeArr = NoticeFactory::fetchNoticeLists($versionCode);
        if (empty($noticeArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $noticeLists = NoticeStrategy::getNoticeLists($indent, $noticeArr);

        return RestResponseFactory::ok($noticeLists);
    }
}