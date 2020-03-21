<?php

namespace App\Models\Chain\Quickloan\Quickloan;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\QuickloanFactory;

/**
 * 徐改点击量
 *
 * Class CheckIsLoginAction
 * @package App\Models\Chain\Quickloan\Quickloan
 */
class UpdateConfigCountAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '点击统计出错！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 验证是否登录
     *
     * @return mixed
     */
    public function handleRequest()
    {
        if ($this->updateConfigCount($this->params) == true) {
            return $this->params;
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
    public function updateConfigCount($params = [])
    {
        return QuickloanFactory::updateConfigClickCountById($params);
    }
}