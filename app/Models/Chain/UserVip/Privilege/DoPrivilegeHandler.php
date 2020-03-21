<?php

namespace App\Models\Chain\UserVip\Privilege;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 特权 - 对接统计
 */
class DoPrivilegeHandler extends AbstractHandler
{
    #外部传参
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     *  1.验证是否需要对接
     *  2.对接 - 返回对接地址
     *  3.统计
     *
     * @return array
     */
    public function handleRequest()
    {
        $result = ['error' => '特权 - 对接统计跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new FetchPrivilegeUrlAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('特权 - 对接统计跳转失败-try');
                logError($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('特权 - 对接统计跳转失败-catch');
            logError($e->getMessage());
        }
        return $result;
    }

}
