<?php

namespace App\Models\Chain\Payment\SubVipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Strategies\UserVipStrategy;

class UpdateVipStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '更新vip状态失败！', 'code' => 1004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:更新vip状态
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateVipStatus($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * 更新订单状态
     */
    private function updateVipStatus($params = [])
    {
        //更新会员状态的时间
        logInfo("LLLLLLLLLLLLL---", $params);
        $uid = PaymentFactory::getUserOrderUid($params['orderid']);
        //根据返回的状态进行vip状态对应
//        $vipStatus = UserVipStrategy::getVipStatus($params['status']);

        $vipStatus = $params['status'];
        if(isset($params['viptype']) && is_numeric($params['viptype'])){
            $params['viptype'] = isset($params['viptype'])?$params['viptype']:$params['vip_type'];
        }else{
            if(isset($params['vip_type']) && strcmp($params['vip_type'],"vip_monthly_member")==0){
                $params['viptype'] = "3";
            }else if(isset($params['vip_type']) && strcmp($params['vip_type'],"vip_quarterly_member")==0){
                $params['viptype'] = "2";
            }else if(isset($params['vip_type']) && strcmp($params['vip_type'],"vip_annual_member")==0){
                $params['viptype'] = "1";
            }else if(isset($params['vip_type']) && is_numeric($params['vip_type'])){
                $params['viptype'] =$params['vip_type'];
            }

        }
       // $params['viptype'] = isset($params['viptype'])?$params['viptype']:$params['vip_type'];
        logInfo('updateVipStatus',[$vipStatus, $params['viptype']]);
        $res = PaymentFactory::updateUserVipSubStatus($uid, $vipStatus, $params['viptype']);

        return $res;
    }

}
