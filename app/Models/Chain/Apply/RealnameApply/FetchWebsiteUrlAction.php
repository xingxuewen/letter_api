<?php

namespace App\Models\Chain\Apply\RealnameApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Strategies\OauthStrategy;
use App\Models\Chain\Apply\RealnameApply\CreateProductApplyLogAction;

class FetchWebsiteUrlAction extends AbstractHandler
{
    private $params = array();
    protected $datas = array();
    protected $error = array('error' => '产品对接！', 'code' => 10007);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 联登获取产品第三方地址
     * @return array|bool
     */
    public function handleRequest()
    {
        //联登获取产品第三方地址
        if ($this->fetchWebsiteUrl($this->params) == true) {
            $this->setSuccessor(new CreateProductApplyLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 联登获取产品第三方地址
     * @param $params
     * @return bool
     */
    private function fetchWebsiteUrl($params)
    {
        $datas = OauthStrategy::getWebsite($params);

        //数据处理
        $this->params['page'] = $datas['url'];

        return true;
    }
}