<?php

namespace App\Models\Chain\Promotion\Register;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use DB;
use App\Models\Chain\Promotion\Register\CreateUserAction;

/**
 * 推广联登-注册
 *
 * Class DoPromotionRegisterHandler
 * @package App\Models\Chain\Register
 */
class DoPromotionRegisterHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     *
     * 第二步:用户主表插入数据
     * 第三步:创建用户认证信息
     * 第四步:创建用户身份信息
     * 第五步:生成用户token值
     * 第六步：返回用户信息
     *
     */

    /**
     *
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CreateUserAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                logError('推广联登-注册, 事务异常-try', $result['error']);
            }
            else
            {
                DB::commit();
            }
        }
        catch (\Exception $e)
        {
	            DB::rollBack();
	
	            logError('推广联登-注册, 事务异常-catch', $e->getMessage());
        }
        return $result;
    }

}
