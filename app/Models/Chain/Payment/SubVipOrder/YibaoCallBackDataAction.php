<?php

namespace App\Models\Chain\Payment\SubVipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Payment\YiBao\YiBaoService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserVipStrategy;

class YibaoCallBackDataAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '返回参数数据不正确！', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:获取回调的传参数数据
     * @return array
     */
    public function handleRequest()
    {
        if ($this->backData($this->params)) {
            $this->setSuccessor(new UpdateOrderStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    public function handleRequest_new()
    {
        if ($this->backData($this->params)) {
            logInfo("hhhhhhhhhhhhhhhhhhhhhhhhhhhhhh=== ", $this->params);
            $this->setSuccessor(new UpdateOrderStatusAction($this->params));
            return $this->getSuccessor()->handleRequest_new();
        } else {
            return $this->error;
        }
    }

    /**
     * 获取返回值
     *
     * @param array $params
     * @return bool
     */
    private function backData($params = [])
    {
        $payNid = isset($params['pay_nid']) ? $params['pay_nid'] : 'YBZF';

        switch ($payNid) //回调渠道
        {
            case 'YBZF':
                $res = $this->yibaoReturnData($params);
                break;
            case 'HJZF':
                $res = $this->huijuReturnData($params);
                break;
            default:
                $res = $this->yibaoReturnData($params);
        }

        return $res;
    }


    /**
     * 易宝支付 - 回调
     *
     * @param array $params
     * @return bool
     */
    private function yibaoReturnData($params = [])
    {
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        $return['vip_type'] = $params['vip_type'];
        //易宝订单状态对应用户vip状态
        $return['status'] = UserVipStrategy::getVipStatus($return['status']);

        if (is_array($return)) {
            $this->params = $return;
            return true;
        } else {
            $this->error = $return;
            return false;
        }
    }

    /**
     * 汇聚支付 - 回调
     *
     * @param array $params
     * @return bool
     */
    private function huijuReturnData($params = [])
    {
        logInfo("ggggggggggggggggggg", $params);
        $return = $params;
        //构造回调返回数据，保证与易宝可公用参数
        $return['orderid'] = $params['r2_OrderNo'];
        $return['yborderid'] = $params['r7_TrxNo'];
        $return['lastno'] = isset($params['lastno']) ? $params['lastno'] : '';
        //支付卡类型 支付卡的类型,1 为借记卡,2 为信用卡 3 微信, 4 支付宝;
        $return['cardtype'] = isset($params['cardtype']) ? $params['cardtype'] : 4;
        $return['amount'] =$params['r3_Amount']; //bcmul($params['r3_Amount'], 100);
        $return['status'] = UserVipStrategy::formatHuijuReturnStatusToVipStatus($params['r6_Status']);

        if (is_array($return)) {
            $this->params = $return;
            return true;
        } else {
            $this->error = $return;
            return false;
        }
    }

}
