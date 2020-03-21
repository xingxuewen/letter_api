<?php

namespace App\Http\Controllers\View;

use App\Constants\UserIdentityConstant;
use App\Constants\UserReportConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Strategies\UserIdentityStrategy;
use App\Strategies\UserReportStrategy;
use Illuminate\Http\Request;

/**
 * 用户
 * Class UserController
 * @package App\Http\Controllers\View
 */
class UserController extends Controller
{
    /**
     * 身份认证——认证协议
     * @return \Illuminate\View\View
     */
    public function fetchIdentityAgreement()
    {
        return view('app.sudaizhijia.agreements.identity_agreement');
    }

    /**
     * 身份认证——认证协议
     * 可配置参数  name=速贷之家&corporate=北京智借网络科技有限公司
     *
     * @return \Illuminate\View\View
     */
    public function fetchIdentityAgreementByParam()
    {
        return view('app.sudaizhijia.agreements.identity_agreement');
    }

    /**
     * 速贷之家——会员协议
     * @return \Illuminate\View\View
     */
    public function fetchMembershipAgreement()
    {
        return view('app.sudaizhijia.agreements.membership_agreement');
    }

    /**
     * 速贷之家——会员协议
     * @return \Illuminate\View\View
     */
    public function fetchMembershipAgreementByParam()
    {
        return view('app.sudaizhijia.agreements.membership_agreement');
    }

    /**
     * 速贷之家APP用户使用协议
     * @return \Illuminate\View\View
     */
    public function fetchUseAgreement()
    {
        return view('app.sudaizhijia.agreements.use_agreement');
    }

    /**
     * 速贷之家APP用户使用协议
     * @return \Illuminate\View\View
     */
    public function fetchUseAgreementByParam()
    {
        return view('app.sudaizhijia.agreements.use_agreement');
    }

    /**
     * 畅行天下升级版保险文档说明
     * @return \Illuminate\View\View
     */
    public function fetchChangxingtianxiaAgreement()
    {
        return view('app.sudaizhijia.agreements.changxingtianxia_agreement');
    }

    /**
     * 北京野二APP用户使用协议
     * @return \Illuminate\View\View
     */
    public function fetchYeerAgreement()
    {
        return view('app.sudaizhijia.agreements.yeer_agreement');
    }
}
