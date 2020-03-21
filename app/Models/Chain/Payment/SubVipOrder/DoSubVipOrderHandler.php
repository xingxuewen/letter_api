<?php

namespace App\Models\Chain\Payment\SubVipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 同步更新会员和订单
 */
class DoSubVipOrderHandler extends AbstractHandler
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
     * 0.获取回调的传参数
     * 1.同步user_order表中的订单状态
     * 2.同步user_vip表中的会员状态、会员有效期期限
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '同步失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new YibaoCallBackDataAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                logError('同步订单会员状态, 事务异常-try', $result['error']);
            }
            else
            {
                DB::commit();

            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            logError('同步订单会员状态, 事务异常-catch', $e->getMessage());
        }

        return $result;
    }

    public function handleRequest_new()
    {
        $result = ['error' => '同步失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new YibaoCallBackDataAction($this->params));
            $result = $this->getSuccessor()->handleRequest_new();
            if (isset($result['error']))
            {
                DB::rollback();

                logError('同步订单会员状态, 事务异常-try', $result['error']);
            }
            else
            {
                DB::commit();

            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            logError('同步订单会员状态, 事务异常-catch', $e->getMessage());
        }

        return $result;
    }

}
