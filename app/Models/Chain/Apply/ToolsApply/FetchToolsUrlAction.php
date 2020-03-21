<?php

namespace App\Models\Chain\Apply\ToolsApply;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\User\Tools\ToolsService;

/**
 * 对接 - 返回对接地址
 *
 * Class FetchWebsiteUrlAction
 * @package App\Models\Chain\Apply\SpreadApply
 */
class FetchToolsUrlAction extends AbstractHandler
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
            $this->setSuccessor(new CreateDataToolsLogAction($this->params));
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
        $tools = $params['tools'];

        $this->params['app_link'] = $tools['app_link'];
        $this->params['h5_link'] = $tools['h5_link'];

        if ($tools && $tools['is_abut'] == 1) //对接
        {
            $response = ToolsService::i()->toToolsService($params);
            $this->params['app_link'] = $response['app_link'];
            $this->params['h5_link'] = $response['h5_link'];
        }

        return true;
    }
}