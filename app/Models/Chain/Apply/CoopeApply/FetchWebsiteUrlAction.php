<?php

namespace App\Models\Chain\Apply\CoopeApply;

use App\Models\Chain\AbstractHandler;
use App\Strategies\OauthStrategy;
use App\Strategies\OneloanProductStrategy;
use App\Models\Chain\Apply\CoopeApply\CreateCoopProductApplyLogAction;

/**
 *
 * Class FetchWebsiteUrlAction
 * @package App\Models\Chain\Apply\OneloanApply
 */
class FetchWebsiteUrlAction extends AbstractHandler
{
    private $params = array();
    protected $data = array();
    protected $error = array('error' => '地址获取出错', 'code' => 10001);

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
        if ($this->fetchWebsiteUrl($this->params) == true)
        {
            $this->setSuccessor(new CreateCoopProductApplyLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchWebsiteUrl($params)
    {
        $urlArr = OauthStrategy::getCoopeWebsite($params);
        $this->params['url'] = $urlArr['url'];

        return $urlArr ? $urlArr : [];
    }
}