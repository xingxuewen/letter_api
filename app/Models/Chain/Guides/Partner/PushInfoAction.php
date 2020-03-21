<?php

namespace App\Models\Chain\Guides\Partner;


use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserPromoteFactory;

class PushInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '推送拍拍贷失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.拍拍贷产品推送
     */
    public function handleRequest()
    {
        if ($this->fetchSpread($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     *
     */
    private function fetchSpread($params = [])
    {dd($this->params);
        if ($params['promotions_nid']) //产品唯一标识集合
        {
            $result = PromotionService::i()->to($params);   //-------预留调用拍拍贷事件----------------
            if ($result) //返回失败状态
            {
                // 更新&插入失败推送表
                UserPromoteFactory::CreateOrUpdatePromotionFail($params);
            }
        }

        return true;
    }
}