<?php

namespace App\Models\Chain\Quickloan\Quickloan;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Quickloan\Quickloan\CheckIsLoginAction;

/**
 * 极速贷
 */
class DoQuickloanHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }


    /**
     * 思路:
     * 1.根据后台配置 验证登录
     *      否   提示登录
     * 1.1 获取跳转地址
     * 2.记极速贷点击流水
     * 3.极速贷总点击量更新
     * 4.返回跳转标识
     */


    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '极速贷跳转出错啦！', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckIsLoginAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('极速贷, 事务异常-try', $result['error']);
            } else {
                DB::commit();

            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('极速贷, 事务异常-catch', $e->getMessage());
        }

        return $result;

    }

}
