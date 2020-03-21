<?php

namespace App\Models\Chain\Oneloan\Basic;

use App\Constants\SpreadConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;

/**
 * 匹配分组
 * Class MatchGroupAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class MatchGroupAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款basic匹配分组失败！', 'code' => 1003);

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
        if ($this->fetchMatchGroup($this->params)) {
            $this->setSuccessor(new CreateUserSpreadRelAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     */
    private function fetchMatchGroup($params = [])
    {
        //3.推送d
        //推送金额界限
        $minlimitMoney = SpreadConstant::SPREAD_MONEY_LIMIT_A;
        if ($params['spread_id'] && isset($params['money']) && $params['money'] < $minlimitMoney) //额度区分
        {
            //推送d组产品
            $groupNid = SpreadConstant::SPREAD_GROUP_D;
            $group = UserSpreadFactory::fetchSpreadGroupByNid($groupNid);
            //获取分组ids
            //logInfo('D组', ['data' => $group]);
            //限制推送数量
            $this->params['groupInfo'] = isset($group) ? $group : [];

            return $group;
        }

        return true;
    }

}
