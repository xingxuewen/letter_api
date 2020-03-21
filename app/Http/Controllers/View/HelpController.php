<?php

namespace App\Http\Controllers\View;

use App\Constants\HelpConstant;
use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Services\AppService;
use Illuminate\Http\Request;

/**
 * 帮助中心、设置相关
 * Class AgreementController
 * @package App\Http\Controllers\View
 */
class HelpController extends Controller
{
    /**
     * 获取协议内容
     * @param Request $request
     * @return \Illuminate\View\View|string
     */
    public function fetchAgreement(Request $request)
    {
        $id = $request->input('id');

        switch ($id) {
            case 1:
                //速贷之家《注册服务协议》
                return view('app.sudaizhijia.agreements.use_agreement');
                break;
            case 2:
                //速贷之家《会员服务协议》
                return view('app.sudaizhijia.agreements.membership_agreement');
                break;
            case 3:
                //闪信《授权协议》
                return view('app.sudaizhijia.agreements.credit_report_agreement');
                break;
            case 4:
                //速贷之家《个人身份认证协议》
                return view('app.sudaizhijia.agreements.identity_agreement');
                break;
            default:
                return '';
        }
    }

    /**
     * 设置 - 协议
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAgreements()
    {
        $agreements = HelpConstant::AGREEMENTS;

        return view('app.sudaizhijia.help.agreements', ['data' => $agreements]);
    }


    /**
     * 设置 - 协议
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAgreementsByParam(Request $request)
    {
        $name = $request->input('name', '速贷之家');
        $corporate = $request->input('corporate', '北京智借网络科技有限公司');

        $agreements = [
            [
                'id' => 1,
                'name' => "《$name" . "用户注册协议》",
                'url' => AppService::API_URL . '/view/v2/users/identity/use?name=' . $name . '&corporate=' . $corporate,
            ],
            [
                'id' => 2,
                'name' => "《$name" . "VIP会员服务协议》",
                'url' => AppService::API_URL . '/view/v2/users/identity/membership?name=' . $name . '&corporate=' . $corporate,
            ],
            [
                'id' => 3,
                'name' => "《$name" . "信用检测授权协议》",
                'url' => AppService::API_URL . '/view/v2/users/report/agreement?name=' . $name . '&corporate=' . $corporate,
            ],
            [
                'id' => 4,
                'name' => "《$name" . "个人身份认证协议》",
                'url' => AppService::API_URL . '/view/users/identity/agreement?name=' . $name . '&corporate=' . $corporate,
            ],
        ];

//        return RestResponseFactory::ok($agreements);
        return view('app.sudaizhijia.help.agreements_v2', ['data' => $agreements]);
    }

    /**
     * 获取协议内容
     * @param Request $request
     * @return \Illuminate\View\View|string
     */
    public function fetchAgreementByParam(Request $request)
    {
        $id = $request->input('id');
        $data['name'] = $request->input('name', '速贷之家');
        $data['corporate'] = $request->input('corporate', '北京智借网络科技有限公司');

        switch ($id) {
            case 1:
                //速贷之家《注册服务协议》
                return view('app.sudaizhijia.agreements.use_agreement', ['data' => $data]);
                break;
            case 2:
                //速贷之家《会员服务协议》
                return view('app.sudaizhijia.agreements.membership_agreement', ['data' => $data]);
                break;
            case 3:
                //闪信《授权协议》
                return view('app.sudaizhijia.agreements.credit_report_agreement', ['data' => $data]);
                break;
            case 4:
                //速贷之家《个人身份认证协议》
                return view('app.sudaizhijia.agreements.identity_agreement', ['data' => $data]);
                break;
            default:
                return '';
        }
    }
}