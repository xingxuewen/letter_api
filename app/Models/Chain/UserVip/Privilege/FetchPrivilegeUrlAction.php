<?php

namespace App\Models\Chain\UserVip\Privilege;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\Tools\ToolsService;
use App\Services\Core\User\Privilege\PrivilegeService;

/**
 * 对接 - 返回对接地址
 *
 * Class FetchWebsiteUrlAction
 * @package App\Models\Chain\Apply\SpreadApply
 */
class FetchPrivilegeUrlAction extends AbstractHandler
{
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 获取产品第三方地址
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->fetchToolsUrl($this->params) == true) {
            $this->setSuccessor(new CreateDataPrivilegeLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchToolsUrl($params)
    {
        $privilege = $params['privilege'];

        $this->params['url'] = $privilege['url'];

        if ($privilege && $privilege['is_abut'] == 1) //对接
        {
            $response = PrivilegeService::i()->toPrivilegeService($params);
            $this->params['url'] = $response['url'];
        }

        return true;
    }
}