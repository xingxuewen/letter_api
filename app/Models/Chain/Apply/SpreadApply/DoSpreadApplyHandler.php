<?php

namespace App\Models\Chain\Apply\SpreadApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Apply\SpreadApply\CheckIsLoginAction;

/**
 * 一键选贷款-申请借款
 */
class DoSpreadApplyHandler extends AbstractHandler
{
    #外部传参
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     *  1.验证是否需要登录
     *  2.验证是否需要认证
     *  3.验证是否对接
     *  4.对接 - 返回对接地址
     *  5.统计
     *
     * @return array
     */
    public function handleRequest()
    {
        $result = ['error' => '一键选贷款-申请借款跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckIsLoginAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('一键选贷款-申请借款跳转失败-try', $result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('一键选贷款-申请借款跳转失败-catch', $e->getMessage());
        }
        return $result;
    }

}
