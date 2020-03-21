<?php

namespace App\Models\Chain\Order\SubVipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 会员订单
 * 根据子类型数据进行购买
 *
 * Class DoSubVipOrderLogicHandler
 * @package App\Models\Chain\Order\SubVipOrder
 */
class DoSubVipOrderLogicHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 思路：
     * 0.检查会员状态
     * 1.添加会员
     * 2.创建订单【订单id】
     * 3.设置本次支付的银行卡
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '会员支付订单创建失败', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckVipStatusAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('vip-订单失败, vip-try', $result['error']);
            } else {
                DB::commit();

            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('vip-订单捕获异常, vip异常-catch', $e->getMessage());
        }

        return $result;
    }

    public function handleRequest_new()
    {
        $result = ['error' => '会员支付订单创建失败', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckVipStatusAction($this->params));
            $result = $this->getSuccessor()->handleRequest_new();
            if (isset($result['error'])) {
                DB::rollback();
                logError('vip-订单失败, vip-try', $result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('vip-订单捕获异常, vip异常-catch', $e->getTraceAsString());
        }

        return $result;
    }

}
