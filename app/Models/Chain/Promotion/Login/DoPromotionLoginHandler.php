<?php

namespace App\Models\Chain\Promotion\Login;

use App\Models\Chain\AbstractHandler;
use DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\Promotion\Login\CheckUserLockAction;

/**
 *联合登录 —— 登录
 *
 * Class DoLoginHandler
 * @package App\Models\Chain\Promotion\Login
 */
class DoPromotionLoginHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 验证码快捷登录
     *
     * 第一步: 检查用户是否被锁定
     * 第三步: 更新用户最后登录时间
     * 第四步: 刷新token
     * 4.1 设置用户身份
     * 第五步: 将联登地址返回
     *
     */

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckUserLockAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                logError('推广联登-登录, 事务异常-try', $result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            logError('推广联登-登录, 事务异常-catch', $e->getMessage());
        }
        return $result;
    }

}
