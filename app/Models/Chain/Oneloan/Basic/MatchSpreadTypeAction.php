<?php

namespace App\Models\Chain\Oneloan\Basic;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;

/**
 * 4.匹配分组产品
 * Class MatchSpreadTypeAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class MatchSpreadTypeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款basic匹配推送产品失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     * @return array
     */
    public function handleRequest()
    {
        if ($this->fetchMatchSpreadType($this->params)) {
            $this->setSuccessor(new SpreadAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    private function fetchMatchSpreadType($params = [])
    {
        if ($params['groupInfo']) //分组
        {
            $group = $params['groupInfo'];
            $this->params['group_type_nid'] = $group['type_nid'];
            $this->params['group_id'] = $group['id'];
            //查询分组产品
            $groupProduct = UserSpreadFactory::fetchSpreadGroupRelTypeById([$group['id']]);
            //获取产品唯一标识
            $this->params['spreadNids'] = UserSpreadFactory::fetchTypeNidsByIds($groupProduct);
        }

        return true;
    }

}
