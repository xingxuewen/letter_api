<?php

namespace App\Models\Chain\Quickloan\Quickloan;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Quickloan\Quickloan\CreateDataLogAction;

/**
 * 获取跳转地址
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Quickloan\Quickloan
 */
class FetchQuickloanUrlAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '对接出错！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 获取跳转地址
     *
     * @return mixed
     */
    public function handleRequest()
    {
        if ($this->fetchQuickloanUrl($this->params) == true) {
            $this->setSuccessor(new CreateDataLogAction($this->params));
            return $this->getSuccessor()->handleRequest();

        } else {
            return $this->error;
        }
    }

    /**
     *
     *
     * @param array $params
     * @return bool
     */
    public function fetchQuickloanUrl($params = [])
    {
        $this->params['url'] = isset($params['config']['url']) ? $params['config']['url'] : '';
        return true;
    }
}