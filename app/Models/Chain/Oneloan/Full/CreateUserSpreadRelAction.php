<?php

namespace App\Models\Chain\Oneloan\Full;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Chain\Oneloan\Full\MatchSpreadTypeAction;

/**
 * 3.用户分组统计
 * Class UpdateUserSpreadAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class CreateUserSpreadRelAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款full用户分组统计失败！', 'code' => 1003);

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
        if ($this->createUserSpreadRel($this->params)) {
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
    private function createUserSpreadRel($params = [])
    {
        if ($params['groupInfo']) //分组存在
        {
            //分组id集合
            $groupIds = UserSpreadFactory::fetchGroupIdsByGroupType($params['group_nid']);
            //统计用户分组
            foreach ($groupIds as $key => $val) {
                $params['group_id'] = $val;
                $res = UserSpreadFactory::createUserSpreadGroupRel($params);
            }

            //关闭该用户下当下不符合的分组条件
            $params['groupIds'] = $groupIds;
            UserSpreadFactory::updateSpreadGroupRelByIds($params);
        }

        return true;
    }

}
