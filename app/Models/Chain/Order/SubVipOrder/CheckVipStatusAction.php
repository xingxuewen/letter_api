<?php

namespace App\Models\Chain\Order\SubVipOrder;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserVipFactory;

/**
 * 检查会员状态
 *
 * Class CheckVipStatusAction
 * @package App\Models\Chain\Order\SubVipOrder
 */
class CheckVipStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '检查会员状态失败！', 'code' => 1002);

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
        if ($this->isVip($this->params)) {
            $this->setSuccessor(new CreateVipAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    public function handleRequest_new()
    {
        if ($this->isVip($this->params)) {
            $this->setSuccessor(new CreateVipAction($this->params));
            return $this->getSuccessor()->handleRequest_new();
        } else {
            return $this->error;
        }
    }

    /**
     * 判断是否可以进行充值
     *
     * @param array $params
     * @return bool
     */
    private function isVip($params = [])
    {
        $vips = UserVipFactory::fetchSubtypeIdByNid($params['subtypeNid']);
        if (empty($vips)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1140), 'code' => 1140];
            return false;
        }

        //将vip信息传入数组中
        $this->params['subVip'] = $vips;

        return true;
    }

}
