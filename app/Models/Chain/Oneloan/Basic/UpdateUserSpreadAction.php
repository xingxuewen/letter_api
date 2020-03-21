<?php

namespace App\Models\Chain\Oneloan\Basic;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Chain\Oneloan\Basic\MatchGroupAction;

/**
 * 1.修改信息
 * Class UpdateUserSpreadAction
 * @package App\Models\Chain\Oneloan\Basic
 */
class UpdateUserSpreadAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '一键选贷款basic修改信息失败！', 'code' => 1003);

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
        if ($this->updateUserSpread($this->params)) {
            $this->setSuccessor(new MatchGroupAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    private function updateUserSpread($params = [])
    {
        //1.插入
        $saveResId = UserSpreadFactory::createOrUpdateUserSpread($params);
        //存储或修改主键id
        $this->params['spread_id'] = $saveResId;

        return $saveResId;
    }

}
