<?php

namespace App\Models\Chain\Oneloan\Basic;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Oneloan\Basic\UpdateUserSpreadAction;

/**
 * 基础信息修改 & 推送
 */
class DoBasicHandler extends AbstractHandler
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
     * １.修改用户信息
     * 2.匹配分组
     * 3.用户分组统计
     * 4.匹配产品
     * 5.推送产品
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => 'basic同步失败', 'code' => 1000];

        try
        {
            $this->setSuccessor(new UpdateUserSpreadAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                logError('修改一键选贷款基础信息&推送产品失败, 事务异常-try', $result['error']);

            }
        }
        catch (\Exception $e)
        {
            logError('修改一键选贷款基础信息&推送产品失败, 事务异常-catch', $e->getMessage());
        }

        return $result;
    }

}
