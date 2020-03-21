<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\OperateFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Wechat\JssdkService;
use Illuminate\Http\Request;

/**
 * Class WechatController
 * @package App\Http\Controllers\V1
 * 微信 JSSDK
 */
class WechatController extends Controller
{
    /**
     * @param Request $request
     * 微信对接 JSSDK
     */
    public function fetchSignPackage(Request $request)
    {
        //接收访问的URL地址
        $url = $request->input('url');
        //对接微信的ID与秘钥
        //$appId = 'wxd6e7d96d8ae7602b';
        $appId = 'wxd6e7d96d8ae7602b';
        //$appSecret = 'cf827897a1390e435955bd352c0f998a';
        $appSecret = '76c8b1fc647e2461d86b3c2b4fc9a91e';
        $obj = new  JssdkService($appId, $appSecret);
        $signPackage = $obj->getSignPackage($url);
        //print_r($signPackage);die;
        return RestResponseFactory::ok($signPackage);
    }

    /**event站 分享
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function fetchEventWechatShare(Request $request)
    {
        //接收访问的URL地址
        $url = $request->input('url');
        //对接微信的ID与秘钥
        $appId = 'wxab78b101369a2c42';
        $appSecret = 'acfab251488f2d0ac18d0db2183ab84c';
        $obj = new  JssdkService($appId, $appSecret);
        $signPackage = $obj->getSignPackage($url);
        //print_r($signPackage);die;
        return RestResponseFactory::ok($signPackage);
    }


    /**
     * 一对一微信弹窗信息
     * 展示微信号、微信二维码
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOneForOneWechat()
    {
        //微信号
        $wechatNum = UserVipConstant::WECHAT_NUMBER;
        $wechat = OperateFactory::fetchProductOperateConfigByNid($wechatNum);
        $data['wechat_num'] = isset($wechat['value']) ? $wechat['value'] : '';

        //微信二维码
        $qrcode = UserVipConstant::WECHAT_QRCODE;
        $wechat = OperateFactory::fetchProductOperateConfigByNid($qrcode);
        $data['wechat_qrcode'] = isset($wechat['logo']) ? QiniuService::getImgs($wechat['logo']) : '';

        //会员特权个数
        //会员状态
        $vipTypeId = UserVipFactory::getVipTypeId();
        //特权类型主id
        $priData['priTypeId'] = UserVipFactory::fetchVipPrivilegeIdByNid(UserVipConstant::VIP_PRIVILEGE_UPGRADE);
        //根据会员查询对应的特权列表ids
        $priData['privilegeIds'] = UserVipFactory::getVipPrivilegeIds($vipTypeId);
        $data['vip_privilege_count'] = UserVipFactory::fetchVipPrivilegeCount($priData);

        return RestResponseFactory::ok($data);
    }

}