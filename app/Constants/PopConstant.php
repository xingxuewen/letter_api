<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 弹窗页面常量定义
 */
class PopConstant extends AppConstant
{
    //推送图片类型
    const GUIDE_PAGE_BANNERS_TYPE = 4;

    //批量弹窗条件
    const PUSH_VERSION_CODE_ONELOAN = ['', 'oneloan'];
    //批量弹窗接口
    const PUSH_VERSION_CODE_ONELOAN_WECHAT = ['', 'oneloan', 'wechat'];

    //默认启动页弹窗
    const PUSH_VERSION_CODE_DEFAULT = [''];

    //启动页弹窗 - 添加小程序
    const PUSH_VERSION_CODE_WECHAT = ['', 'wechat'];
}