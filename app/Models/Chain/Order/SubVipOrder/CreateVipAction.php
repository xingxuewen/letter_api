<?php

namespace App\Models\Chain\Order\SubVipOrder;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserVipFactory;
use App\Strategies\UserVipStrategy;

/**
 * 添加会员
 * 还未进行支付  会员状态 4 处理中
 *
 * Class CreateVipAction
 * @package App\Models\Chain\Order\SubVipOrder
 */
class CreateVipAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '创建vip失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:创建vip
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createVip($this->params)) {
            $this->setSuccessor(new CreateVipOrderAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 第三步:创建vip
     * @return array
     *   by xuyj  v3.2.3
     */
    public function handleRequest_new()
    {
        if ($this->createVip($this->params)) {
            $this->setSuccessor(new CreateVipOrderAction($this->params));
            return $this->getSuccessor()->handleRequest_new();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建或修改会员信息
     * 状态 4 处理中
     *
     * @param array $params
     * @return bool
     */
    private function createVip($params = [])
    {
        //添加或更改会员信息
        $vips['vip_type'] = $params['subVip']['vip_type'];
        $vips['user_id'] = $params['user_id'];
        $vips['vip_no'] = UserVipStrategy::generateId(UserVipFactory::getVipLastId());
        //子类型id
        $vips['subtype_id'] = $params['subVip']['id'];
        //$vips['end_time'] = UserVipStrategy::getVipExpired();
        $createVip = UserVipFactory::createOrUpdateUserVip($vips);
        if (!$createVip) {
            $this->error = ['error' => RestUtils::getErrorMessage(1137), 'code' => 1137];
            return false;
        }

        return $createVip;
    }

}
