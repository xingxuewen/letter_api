<?php

namespace App\Models\Chain\Apply\ToolsApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 工具集 - 对接统计
 */
class DoToolsApplyHandler extends AbstractHandler
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
     *  3.验证是否对接
     *  4.对接 - 返回对接地址
     *  5.统计
     *
     * @return array
     */
    public function handleRequest()
    {
        $result = ['error' => '工具集 - 对接统计跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckIsAuthAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('工具集 - 对接统计跳转失败-try', $result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('工具集 - 对接统计跳转失败-catch', $e->getMessage());
        }
        return $result;
    }

}
