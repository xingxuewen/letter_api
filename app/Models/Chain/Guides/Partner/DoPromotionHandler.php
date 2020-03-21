<?php

namespace App\Models\Chain\Guides\Partner;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;

/**
 * 基础信息认证 & 推送
 */
class DoPromotionHandler extends AbstractHandler
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
     * 1.天创三要素认证
     * 2.用户信息添加&修改到流水表
     * 3.拍拍贷产品推送
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {//dd($this->params);
        $result = ['error' => '产品推送失败', 'code' => 1000];

        try
        {
            $this->setSuccessor(new VerifyInfo3CAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                logError('天创三要素认证&推送产品失败, 事务异常-try', $result['error']);
            }
        }
        catch (\Exception $e)
        {
            logError('天创三要素认证&推送产品失败, 事务异常-catch', $e->getMessage());
        }

        return $result;
    }
}