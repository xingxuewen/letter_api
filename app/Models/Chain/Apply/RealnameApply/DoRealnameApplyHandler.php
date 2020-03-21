<?php

namespace App\Models\Chain\Apply\RealnameApply;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 申请借款
 */
class DoRealnameApplyHandler extends AbstractHandler
{
    #外部传参
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 1.验证是否需要实名                    Y：验证是否已经实名，已经实名执行 2 ，未实名直接返回 N：执行 2
     * 2.验证是否需要撞库                    Y：执行撞库，撞库成功执行 3 ，撞库失败直接返回 N：执行 6
     * 3.判断用户是否是通过速贷之家推的新用户  Y：执行 5 N：执行 4
     * 4.验证是否符合规定的结算模式，         Y：执行 5 N：直接返回
     * 5.验证是否符合资质，                  Y：执行 6 N：直接返回
     * 6.联登获取产品第三方地址
     * 7.创建流水
     * 8.修改统计
     * @return array
     */
    public function handleRequest()
    {
        $result = ['error' => '实名-立即申请跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckIsAbutAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('实名-立即申请跳转出错-try', $result['error']);
            }
            else
            {
                DB::commit();
            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            logError('实名-立即申请跳转出错-catch', [$e->getMessage(), $e->getTraceAsString()]);
        }
        return $result;
    }

}
