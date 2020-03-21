<?php

namespace App\Models\Chain\Apply\RealnameApply;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\PlatformButt\PlatformButtService;

class FetchPlatformButtAction extends AbstractHandler
{
    private $params = array();
    protected $datas = array();
    protected $error = array('error' => '撞库失败！', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 执行撞库，得到地址
     * @return array|bool
     */
    public function handleRequest()
    {
        //执行撞库，得到地址
        if ($this->fetchPlatformButt($this->params) == true) {
            $this->setSuccessor(new CheckIsAuthenAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 执行撞库，得到地址
     * @param $params
     * @return bool
     */
    private function fetchPlatformButt($params)
    {
        $butt = PlatformButtService::i()->toPlatformButtService($params);

        //撞库返回数据处理
        $this->params['is_butt'] = isset($butt['is_butt']) ? $butt['is_butt'] : 0;
        $this->params['is_new_user'] = isset($butt['is_new_user']) ? (empty($butt['is_new_user']) ? 3 : $butt['is_new_user']) : 99;
        $this->params['qualify_status'] = isset($butt['qualify_status']) ? $butt['qualify_status'] : 99;

        return $butt;
    }
}