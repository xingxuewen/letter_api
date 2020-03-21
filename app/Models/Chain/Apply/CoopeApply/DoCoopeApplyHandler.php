<?php

namespace App\Models\Chain\Apply\CoopeApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Apply\CoopeApply\FetchWebsiteUrlAction;

/**
 * 合作贷配置列表 - 申请借款
 */
class DoCoopeApplyHandler extends AbstractHandler
{

    #外部传参
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '合作贷-立即申请跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new FetchWebsiteUrlAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('合作贷-立即申请跳转失败-try', $result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('合作贷-立即申请跳转失败-catch', $e->getMessage());
        }
        return $result;
    }
}