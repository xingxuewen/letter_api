<?php

namespace App\Models\Chain\Apply\RealnameApply;
use App\Models\Chain\AbstractHandler;

/**
 * 验证是否需要撞库
 *
 * Class CheckIsAbutAction
 * @package App\Models\Chain\Apply\RealnameApply
 */
class CheckIsButtAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '验证是否需要撞库！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 验证是否需要撞库
     * @return array|bool
     */
    public function handleRequest()
    {
        //如果需要撞库
        if ($this->checkIsButt($this->params) == true) {
            //执行撞库，经过后续验证，然后联登返回h5地址
            $this->setSuccessor(new FetchPlatformButtAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            //直接联登返回h5地址
            $this->setSuccessor(new FetchWebsiteUrlAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
    }

    /**
     * 判断是否需要撞库
     * @param array $params
     * @return bool
     */
    public function checkIsButt($params = [])
    {
        $product = $params['product'];
        if (isset($product['is_butt']) && $product['is_butt'] == 1)
        {
            return true;
        }

        return false;
    }
}