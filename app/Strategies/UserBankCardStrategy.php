<?php
/**
 * Created by PhpStorm.
 * User: zengqiang
 * Date: 17-10-26
 * Time: 下午5:02
 */

namespace App\Strategies;

use App\Constants\UserBankcardConstant;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\Admin\User\UserRealNameFactory;
use App\Models\Factory\BankFactory;
use App\Models\Factory\PaymentFactory;
use App\Models\Orm\UserRealname;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Models\Factory\UserBankCardFactory;

class UserBankCardStrategy extends AppStrategy
{

    /**
     * 加密银行卡号展示
     *
     * @param $cardnum
     * @return string
     */
    public static function formatCardNum($cardnum)
    {
        if (empty($cardnum)) {
            return '';
        }

        return substr($cardnum, 0, 4) . '****************' . substr($cardnum, -4);
    }

    /**
     * 截取银行卡后四位
     *
     * @param $cardnum
     * @return string
     */
    public static function getCardLastNum($cardnum)
    {
        if (empty($cardnum)) {
            return '';
        }

        return substr($cardnum, -4);
    }

    /**
     * 补充一些信息
     *
     * @param array $data
     * @return array
     */
    public static function getBackBankInfo($data = [])
    {
        $params = [];
        foreach ($data['list'] as $key => $val) {
            $bankinfo = BankFactory::fetchBankinfoById($val['bank_id']);
            $params[$key]['user_bank_id'] = $val['id'];
            $params[$key]['bank_name'] = empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'];
            $params[$key]['bank_logo'] = QiniuService::getImgs($bankinfo['litpic']);
            $params[$key]['account'] = UserBankCardStrategy::formatCardNum($val['account']);
            $params[$key]['last_num'] = UserBankCardStrategy::getCardLastNum($val['account']);
            $params[$key]['card_type_name'] = UserBankCardStrategy::getBankCardTypeName($val['card_type']);
            $params[$key]['card_last_status'] = $val['card_last_status'];
            if (empty($data['userBankId'])) {
                $params[$key]['card_last_pay_status'] = $val['card_last_status'];
            } else {
                $params[$key]['card_last_pay_status'] = $val['id'] == $data['userBankId'] ? 1 : 0;
            }

        }

        return $params ? $params : [];
    }

    /**
     * 补充一些信息
     *
     * @param array $data
     * @return array
     *  by xuyj v3.2.3
     */
    public static function getBackBankInfo_new($data = [],$dbankcardid)
    {
        $params = [];
        $cardList = array();
        foreach ($data['list'] as $key => $val) {

            $res = UserBankCardFactory::getRealnamebyUserid($data['userId']);
            if(!empty($res)){

                $params[$key]['name'] = $res['realname'];
                $params[$key]['idcard'] = $res['certificate_no'];
            }else{
                $params[$key]['name'] = "";
                $params[$key]['idcard'] = "";
            }

            $bankinfo = BankFactory::fetchBankinfoById($val['bank_id']);
            $params[$key]['user_bank_id'] = $val['id'];
            if(strcmp($params[$key]['user_bank_id'],$dbankcardid)==0){
                $params[$key]['ischeck'] = "1";
            }else{
                $params[$key]['ischeck'] = "0";
            }
            $params[$key]['bank_name'] = empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'];
            $params[$key]['bank_logo'] = QiniuService::getImgs($bankinfo['litpic']);
            $params[$key]['account'] = UserBankCardStrategy::formatCardNum($val['account']);
            $params[$key]['account_all'] = $val['account'];
            $params[$key]['last_num'] = UserBankCardStrategy::getCardLastNum($val['account']);
            $params[$key]['card_type_name'] = UserBankCardStrategy::getBankCardTypeName($val['card_type']);
            $params[$key]['card_last_status'] = $val['card_last_status'];
            $params[$key]['card_paycount'] =$val['huiju_paycount'];//empty($val[$key]['huiju_paycount'])?"1":$val[$key]['huiju_paycount'];
            $params[$key]['cvv2'] = $val['cvv2'];
            $params[$key]['avatime'] = $val['avatime'];
            $params[$key]['mobile'] = $val['card_mobile'];
            $params[$key]['bank_id'] = $val['bank_id'];
            if(isset($params[$key]['cvv2'])){
                if(strcmp($params[$key]['cvv2'],"0")==0){
                    $params[$key]['cvv2']="";
                }
            }

            logInfo("hhhhhhhhhhhhhhhhhhh");
            if(isset($params[$key]['avatime'])){
                if(strcmp($params[$key]['avatime'],"0")==0){
                    $params[$key]['avatime']="";
                }
            }

            if (empty($data['userBankId'])) {
                $params[$key]['card_last_pay_status'] = $val['card_last_status'];
            } else {
                $params[$key]['card_last_pay_status'] = $val['id'] == $data['userBankId'] ? 1 : 0;
            }
            if(!empty($val['bank_name'])){
                $resBk = UserBankCardFactory::fetchCurCardisInHuiJu($val['bank_name']);
                if(!empty($resBk)){
                 //   array_push($cardList,$params[$key]);
                    $params[$key]['bkcolor'] = $resBk['bkcolor'];
                    array_push($cardList,$params[$key]);
                 //   array_push($cardList,$params[$key]);
                 //   array_push($cardList,$params[$key]);
                }
            }
        }

        return $cardList ? $cardList : [];
    }

    /**
     * 获取银行卡类型名称
     *
     * @param int $cardType 存银行卡类型
     * @return string
     */
    public static function getBankCardTypeName($cardType)
    {
        switch ($cardType) {
            case 1:
                $cardName = '储蓄卡';
                break;
            case 2:
                $cardName = '信用卡';
                break;
            default:
                $cardName = '';
        }

        return $cardName;
    }

    /**
     * 格式化银行列表银行卡号
     * @param array $params
     * @return array
     */
    public static function getUserbanksinfo($params = [])
    {
        foreach ($params as $key => $val) {
            $params[$key]['account'] = UserBankCardStrategy::formatCardNum($val['account']);
        }

        return $params ? $params : [];
    }

    /**
     * 上次支付银行卡信息
     * @param $data
     * @return array
     */
    public static function getPaymentBank($data)
    {
        $bankinfo = isset($data['bank_id']) ? BankFactory::fetchBankinfoById($data['bank_id']) : [];
        $params['user_bank_id'] = isset($data['id']) ? $data['id'] : 0;
        $params['bank_id'] = isset($data['bank_id']) ? $data['bank_id'] : 0;
        $params['bank_name'] = !empty($bankinfo) ? $bankinfo['sname'] : '';
        $params['bank_logo'] = !empty($bankinfo) ? QiniuService::getImgs($bankinfo['litpic']) : '';
        $params['account'] = isset($data['account']) ? UserBankCardStrategy::formatCardNum($data['account']) : '';
        $params['last_num'] = isset($data['account']) ? UserBankCardStrategy::getCardLastNum($data['account']) : '';
        $params['card_type_name'] = isset($data['card_type']) ? UserBankCardStrategy::getBankCardTypeName($data['card_type']) : '';
        $params['card_last_status'] = isset($data['card_last_status']) ? $data['card_last_status'] : 0;
      //  $params['cvv2'] = $data['cvv2'];
     //   $params['avatime'] = $data['avatime'];
        return $params ? $params : [];
    }


    /**
     * 上次支付银行卡信息
     * @param $data
     * @return array
     */
    public static function  getPaymentBank_new($data,$userId)
    {
        logInfo("7777777777777777777777777");

        $bankinfo = isset($data['bank_id']) ? BankFactory::fetchBankinfoById($data['bank_id']) : [];
        $params['user_bank_id'] = isset($data['id']) ? $data['id'] : 0;
        $params['bank_id'] = isset($data['bank_id']) ? $data['bank_id'] : 0;
        $params['bank_name'] = !empty($bankinfo) ? $bankinfo['sname'] : '';
        $params['bank_logo'] = !empty($bankinfo) ? QiniuService::getImgs($bankinfo['litpic']) : '';
        $params['account'] = isset($data['account']) ? UserBankCardStrategy::formatCardNum($data['account']) : '';
        $params['last_num'] = isset($data['account']) ? UserBankCardStrategy::getCardLastNum($data['account']) : '';
        $params['card_type_name'] = isset($data['card_type']) ? UserBankCardStrategy::getBankCardTypeName($data['card_type']) : '';
        $params['card_last_status'] = isset($data['card_last_status']) ? $data['card_last_status'] : 0;
        $params['cvv2'] = $data['cvv2'];
        $params['avatime'] = $data['avatime'];
        $params['hjpaycount'] = $data['huiju_paycount'];
        $params['mobile'] = $data['card_mobile'];
        $params['account_all'] = $data['account'];

        $res = UserBankCardFactory::getRealnamebyUserid($userId);
        logInfo("7777777775555555777777777777");
        if(!empty($res)){

            $params['name'] = $res['realname'];
            $params['idcard'] = $res['certificate_no'];
        }else{
            $params['name'] = "";
            $params['idcard'] = "";
        }
        return $params ? $params : [];
    }

    /**
     * 天创验证返回错误提示信息
     * @param array $params
     * @return mixed
     */
    public static function getTianErrorMeg($params = [])
    {
        //result Int 认证结果 1 认证成功 2 认证失败 3 未认证 4 已注销
        if ($params['result'] == 1) {
            return $data = ['error_meg' => $params['resultMsg'], 'error_code' => $params['result']];
        } else {
            return $data = ['error_meg' => $params['detailMsg'], 'error_code' => $params['result']];
        }
    }


    /**
     * 支持银行及限额
     * 根据版本返回h5页面地址
     *
     * @param $data
     * @return string
     */
    public static function getQuotaBankLink($data)
    {
        $versionType = $data['versionType'];
        switch ($versionType) {
            case UserBankcardConstant::QUOTA_BANK_VERSION_TYPE:
                if ($data['cardType'] == 1) {
                    //储蓄卡
                    $quota_bank_link = LinkUtils::quotaSavingCardBankLinkHj();
                } else {
                    //信用卡
                    $quota_bank_link = LinkUtils::quotaCreditCardBankLinkHj();
                }
                break;
            default:
                if ($data['cardType'] == 1) {
                    //储蓄卡
                    $quota_bank_link = LinkUtils::quotaSavingCardBankLink();
                } else {
                    //信用卡
                    $quota_bank_link = LinkUtils::quotaCreditCardBankLink();
                }
        }

        return $quota_bank_link;
    }
}