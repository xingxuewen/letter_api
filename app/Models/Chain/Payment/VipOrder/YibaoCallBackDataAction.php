<?php

namespace App\Models\Chain\Payment\VipOrder;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\Payment\YiBao\YiBaoService;

class YibaoCallBackDataAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '返回参数数据不正确！', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:获取回调的传参数数据
     * @return array
     */
    public function handleRequest()
    {
        if ($this->backData($this->params))
        {
            $this->setSuccessor(new UpdateOrderStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    public function handleRequest_new()
    {
        if ($this->backData($this->params))
        {
            $this->setSuccessor(new UpdateOrderStatusAction($this->params));
            return $this->getSuccessor()->handleRequest_new();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 获取返回值
     */
    private function backData($params = [])
    {
        $return = YiBaoService::i()->undoData($params['data'], $params['encryptkey']);
        $return['vip_type'] = $params['vip_type'];
        if(is_array($return))
        {
            $this->params = $return;
            return true;
        }
        else
        {
            $this->error = $return;
            return false;
        }
    }

}
